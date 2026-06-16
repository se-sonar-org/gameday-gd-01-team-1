const API = 'api.php';

// Load all posts and render them into the page.
async function loadPosts() {
    const container = document.getElementById('posts');

    try {
        const res = await fetch(API);
        const posts = await res.json();

        if (!posts.length) {
            container.innerHTML = '<p class="loading">No posts yet. Be the first to write one!</p>';
            return;
        }

        container.innerHTML = posts.map(renderPost).join('');
    } catch (err) {
        container.innerHTML = '<p class="loading">Failed to load posts.</p>';
    }
}

// Build the HTML for a single post.
function renderPost(post) {
    return `
        <article class="post">
            <h2>${post.title}</h2>
            <p class="meta">by ${post.author} on ${post.created_at}</p>
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
loadPosts();
