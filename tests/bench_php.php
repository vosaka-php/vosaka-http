<?php

declare(strict_types=1);

require __DIR__ . "/../vendor/autoload.php";

use vennv\vosaka\libsql\AsyncMysqlConnection;
use vosaka\foroutines\AsyncMain;
use vosaka\foroutines\Launch;
use vosaka\foroutines\Thread;
use vosaka\foroutines\RunBlocking;

// ─── Config ──────────────────────────────────────────────────────────
const DB_HOST     = '127.0.0.1';
const DB_PORT     = 3307;
const DB_USER     = 'root';
const DB_PASS     = 'pokiwa0981';
const DB_NAME     = 'shop';

const CONCURRENT  = 50;   // concurrent fibers
const ITERATIONS  = 200;  // total queries per scenario

// ─── Helpers ─────────────────────────────────────────────────────────

function makeConn(): AsyncMysqlConnection
{
    $conn = new AsyncMysqlConnection(DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_NAME);
    $conn->connect()->await();
    return $conn;
}

function bench(string $label, callable $fn): void
{
    // warmup
    $fn();

    $start = hrtime(true);
    $fn();
    $elapsed = (hrtime(true) - $start) / 1e6; // ms

    $qps = round(ITERATIONS / ($elapsed / 1000), 1);
    echo str_pad($label, 35) . " | " .
         str_pad(round($elapsed, 2) . " ms", 12) .
         " | {$qps} q/s\n";
}

// ─── Main ─────────────────────────────────────────────────────────────

#[AsyncMain]
function main(): void
{
    echo "\n";
    echo "PHP vosaka-foroutines — MySQL Benchmark\n";
    echo "Concurrent fibers : " . CONCURRENT  . "\n";
    echo "Total iterations  : " . ITERATIONS  . "\n";
    echo str_repeat("─", 62) . "\n";
    echo str_pad("Scenario", 35) . " | " .
         str_pad("Time", 12)     . " | Throughput\n";
    echo str_repeat("─", 62) . "\n";

    // ── 1. Sequential SELECT (single connection) ──────────────────────
    bench("Sequential SELECT (×" . ITERATIONS . ")", function () {
        $conn = makeConn();
        for ($i = 1; $i <= ITERATIONS; $i++) {
            $conn->query('SELECT * FROM users WHERE id = ?', [$i % 10 + 1])->await();
        }
        $conn->close()->await();
    });

    // ── 2. Concurrent SELECT (N fibers, each does ITERATIONS/N queries) 
    bench("Concurrent SELECT (×" . CONCURRENT . " fibers)", function () {
        RunBlocking::new(function () {
            $perFiber = (int) ceil(ITERATIONS / CONCURRENT);
            for ($f = 0; $f < CONCURRENT; $f++) {
                $offset = $f;
                Launch::new(function () use ($perFiber, $offset) {
                    $conn = makeConn();
                    for ($i = 0; $i < $perFiber; $i++) {
                        $conn->query('SELECT * FROM users WHERE id = ?', [($offset + $i) % 10 + 1])->await();
                    }
                    $conn->close()->await();
                });
            }
            Thread::wait();
        });
    });

    // ── 3. Sequential INSERT ──────────────────────────────────────────
    bench("Sequential INSERT (×" . ITERATIONS . ")", function () {
        $conn = makeConn();
        for ($i = 0; $i < ITERATIONS; $i++) {
            $conn->execute(
                'INSERT INTO users (name) VALUES (?)',
                ['BenchUser_' . $i]
            )->await();
        }
        $conn->close()->await();
    });

    // ── 4. Concurrent INSERT ──────────────────────────────────────────
    bench("Concurrent INSERT (×" . CONCURRENT . " fibers)", function () {
        RunBlocking::new(function () {
            $perFiber = (int) ceil(ITERATIONS / CONCURRENT);
            for ($f = 0; $f < CONCURRENT; $f++) {
                $fid = $f;
                Launch::new(function () use ($perFiber, $fid) {
                    $conn = makeConn();
                    for ($i = 0; $i < $perFiber; $i++) {
                        $conn->execute(
                            'INSERT INTO users (name) VALUES (?)',
                            ["Fiber{$fid}_Row{$i}"]
                        )->await();
                    }
                    $conn->close()->await();
                });
            }
            Thread::wait();
        });
    });

    // ── 5. Mixed READ + WRITE (concurrent) ───────────────────────────
    bench("Mixed R+W (×" . CONCURRENT . " fibers)", function () {
        RunBlocking::new(function () {
            $perFiber = (int) ceil(ITERATIONS / CONCURRENT);
            for ($f = 0; $f < CONCURRENT; $f++) {
                $fid = $f;
                Launch::new(function () use ($perFiber, $fid) {
                    $conn = makeConn();
                    for ($i = 0; $i < $perFiber; $i++) {
                        if ($i % 2 === 0) {
                            $conn->query('SELECT * FROM users WHERE id = ?', [$i % 10 + 1])->await();
                        } else {
                            $conn->execute(
                                'INSERT INTO users (name) VALUES (?)',
                                ["Mixed{$fid}_{$i}"]
                            )->await();
                        }
                    }
                    $conn->close()->await();
                });
            }
            Thread::wait();
        });
    });

    echo str_repeat("─", 62) . "\n";
    echo "Done.\n\n";
}
