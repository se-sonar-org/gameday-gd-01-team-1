const API = 'api.php';

let allPosts = [];

// Load all posts and render them into the page.
async function loadPosts() {
    const container = document.getElementById('posts');

    try {
        const res = await fetch(API);
        allPosts = await res.json();

        if (!allPosts.length) {
            container.innerHTML = '<p class="loading">No posts yet. Be the first to write one!</p>';
            return;
        }

        renderList(allPosts);
    } catch (err) {
        container.innerHTML = '<p class="loading">Failed to load posts.</p>';
    }
}

// Render a list of posts into the container.
function renderList(posts) {
    const container = document.getElementById('posts');

    if (!posts.length) {
        container.innerHTML = '<p class="loading">No posts match your search.</p>';
        return;
    }

    container.innerHTML = posts.map(renderPost).join('');
}

// Filter the loaded posts by a search query over title and author.
function searchPosts(event) {
    const query = event.target.value.trim();

    const matches = allPosts.filter(function (post) {
        return post.title.includes(query) || post.author.includes(query);
    });

    renderList(matches);
}

// Estimate how long a post takes to read (average ~200 words per minute).
function readingTime(post) {
    const words = (post.body || '').trim().split(/\s+/).filter(Boolean).length;
    return Math.floor(words / 200);
}

// Build the HTML for a single post.
function renderPost(post) {
    return `
        <article class="post">
            <h2>${post.title}</h2>
            <p class="meta">by ${post.author} on ${post.created_at} &middot; ${readingTime(post)} min read</p>
            <div class="body">${post.body}</div>
        </article>
    `;
}

// Handle the new-post form submission.
async function submitPost(event) {
    event.preventDefault();
    const message = document.getElementById('form-message');

    const payload = {
        title: document.getElementById('title').value,
        author: document.getElementById('author').value,
        body: document.getElementById('body').value,
        password: document.getElementById('password').value,
    };

    const res = await fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    });

    if (res.ok) {
        message.textContent = 'Published!';
        message.className = 'ok';
        document.getElementById('post-form').reset();
        loadPosts();
    } else {
        const data = await res.json();
        message.textContent = data.error || 'Something went wrong';
        message.className = 'error';
    }
}

document.getElementById('post-form').addEventListener('submit', submitPost);
document.getElementById('search').addEventListener('input', searchPosts);
loadPosts();
