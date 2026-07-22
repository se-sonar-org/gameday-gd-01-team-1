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
            <button class="delete" data-id="${post.id}" data-author="${post.author}">Delete</button>
        </article>
    `;
}

// Remove a post. The author is passed so the server can confirm ownership.
async function deletePost(id, author) {
    const url = `${API}?id=${encodeURIComponent(id)}&author=${encodeURIComponent(author)}`;
    const res = await fetch(url, { method: 'DELETE' });
    if (res.ok) {
        loadPosts();
    }
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

document.getElementById('posts').addEventListener('click', (event) => {
    const btn = event.target.closest('button.delete');
    if (btn) {
        deletePost(btn.dataset.id, btn.dataset.author);
    }
});

loadPosts();
