async function getData() {
    const url = "https://jsonplaceholder.typicode.com/posts/1";
    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }

        const json = await response.json();
        console.log(json);
    } catch (error) {
        console.error(error.message);
    }
    console.timeEnd();
}

console.time();
getData();