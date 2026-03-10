// server.js — Node.js port of the VOsaka PHP HTTP server example
'use strict';

const http = require('http');

// ─── Helpers ────────────────────────────────────────────────────────────────

function jsonResponse(data, status = 200) {
    return {
        status,
        headers: { 'Content-Type': 'application/json; charset=utf-8' },
        body: JSON.stringify(data),
    };
}

// ─── Router ─────────────────────────────────────────────────────────────────

class Router {
    constructor() {
        this.routes = [];
    }

    get(path, handler, name = '') {
        this._add('GET', path, handler, name);
        return this;
    }

    _add(method, path, handler, name) {
        const paramNames = [];
        const regexStr = path.replace(/\{(\w+)(?::([^}]+))?\}/g, (_, paramName, paramRegex) => {
            paramNames.push(paramName);
            return `(${paramRegex || '[^/]+'})`;
        });
        this.routes.push({
            method,
            pattern: new RegExp(`^${regexStr}$`),
            paramNames,
            handler,
            name,
        });
    }

    match(method, pathname) {
        for (const route of this.routes) {
            if (route.method !== method) continue;
            const m = pathname.match(route.pattern);
            if (!m) continue;
            const params = {};
            route.paramNames.forEach((name, i) => { params[name] = m[i + 1]; });
            return { handler: route.handler, params };
        }
        return null;
    }
}

// ─── Middleware ──────────────────────────────────────────────────────────────

function corsMiddleware(req, res, next) {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    res.setHeader('Access-Control-Max-Age', '86400');

    if (req.method === 'OPTIONS') {
        res.writeHead(204, { 'Content-Length': '0' });
        res.end();
        return;
    }

    next();
}

function faviconMiddleware(req, res, next) {
    if (req.url === '/favicon.ico') {
        res.writeHead(204);
        res.end();
        return;
    }
    next();
}

// ─── Request handler (compose middlewares + router) ──────────────────────────

function composeMiddlewares(middlewares, finalHandler) {
    return function (req, res) {
        let i = 0;
        function next() {
            if (i < middlewares.length) {
                middlewares[i++](req, res, next);
            } else {
                finalHandler(req, res);
            }
        }
        next();
    };
}

// ─── Routes ─────────────────────────────────────────────────────────────────

const router = new Router()
    .get(
        '/users/{id:\\d+}',
        (req) => jsonResponse({
            user_id: req.params.id,
            message: 'User found with ID: ' + req.params.id,
        }),
        'user.show',
    )
    .get(
        '/posts/{slug}',
        (req) => jsonResponse({
            post_slug: req.params.slug,
            message: 'Post found with slug: ' + req.params.slug,
        }),
        'post.show',
    )
    .get(
        '/',
        (_req) => jsonResponse({
            message: 'Welcome to VOsaka HTTP API',
            version: '2.0',
            routes: {
                'GET /': 'This welcome message',
                'GET /users/{id}': 'Get user by ID (numeric only)',
                'GET /posts/{slug}': 'Get post by slug',
                'GET /health': 'Health check endpoint',
            },
            examples: ['/users/123', '/posts/hello-world', '/health'],
        }),
        'home',
    )
    .get(
        '/health',
        (_req) => jsonResponse({
            status: 'healthy',
            timestamp: new Date().toISOString(),
            uptime: 'Server is running',
        }),
        'health',
    );

// ─── Core dispatcher ─────────────────────────────────────────────────────────

// FIX 1: Pre-build static error buffers once — tránh JSON.stringify +
// Buffer.byteLength allocate trên mỗi error request trong hot loop.
const NOT_FOUND_BODY = Buffer.from(JSON.stringify({ error: 'Not Found' }));
const SERVER_ERROR_BODY = Buffer.from(JSON.stringify({ error: 'Internal Server Error' }));

// FIX 2: Cache Date header, refresh mỗi giây.
// new Date().toUTCString() trong hot path tốn CPU vì allocate string mới mỗi request.
let cachedDate = new Date().toUTCString();
setInterval(() => { cachedDate = new Date().toUTCString(); }, 1000).unref();

function dispatch(req, res) {
    // FIX 3: Guard destroyed socket — đây là nguyên nhân 1,558 write errors.
    if (res.destroyed) return;

    // FIX 4: Tránh `new URL(...)` chỉ để lấy pathname.
    // indexOf + slice rẻ hơn nhiều so với URL constructor.
    const raw = req.url;
    const qIdx = raw.indexOf('?');
    const pathname = qIdx === -1 ? raw : raw.slice(0, qIdx);

    const match = router.match(req.method, pathname);

    if (!match) {
        res.writeHead(404, {
            'Content-Type': 'application/json; charset=utf-8',
            'Content-Length': NOT_FOUND_BODY.byteLength,
            'Server': 'VOsaka-HTTP/2.0',
            'Date': cachedDate,
        });
        res.end(NOT_FOUND_BODY);
        return;
    }

    req.params = match.params;

    try {
        const response = match.handler(req);
        // FIX 5: Convert body sang Buffer một lần — byteLength trở thành
        // .length property read thay vì re-encode scan.
        const body = Buffer.from(response.body ?? '');
        res.writeHead(response.status, {
            ...response.headers,
            'Content-Length': body.byteLength,
            'Server': 'VOsaka-HTTP/2.0',
            'Date': cachedDate,
        });
        res.end(body);
    } catch (err) {
        console.error('[ERROR]', err);
        if (!res.headersSent && !res.destroyed) {
            res.writeHead(500, {
                'Content-Type': 'application/json; charset=utf-8',
                'Content-Length': SERVER_ERROR_BODY.byteLength,
            });
            res.end(SERVER_ERROR_BODY);
        }
    }
}

// ─── Server ──────────────────────────────────────────────────────────────────

const HOST = '0.0.0.0';
const PORT = 8888;

const middlewares = [corsMiddleware, faviconMiddleware];
const requestHandler = composeMiddlewares(middlewares, dispatch);

// FIX 6: Tune keep-alive và headers timeout.
const server = http.createServer({
    keepAliveTimeout: 5000,
    headersTimeout: 10000,
}, requestHandler);

// FIX 7: Tắt Nagle's algorithm — giảm latency dưới load cao.
server.on('connection', (socket) => {
    socket.setNoDelay(true);
});

console.log('=== HTTP Server Examples ===');
console.log(`Starting server on ${HOST}:${PORT}...`);

server.listen(PORT, HOST, () => {
    console.log(`Listening on http://${HOST}:${PORT}`);
    console.log('Routes:');
    console.log('  GET /');
    console.log('  GET /users/{id}   (numeric only)');
    console.log('  GET /posts/{slug}');
    console.log('  GET /health');
});

// Graceful shutdown
process.on('SIGINT', () => { console.log('\nShutting down...'); server.close(() => process.exit(0)); });
process.on('SIGTERM', () => { server.close(() => process.exit(0)); });
