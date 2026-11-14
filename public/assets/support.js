const thread = document.querySelector('[data-ticket-url]');
if (thread?.dataset.ticketUrl) {
    const messages = thread.querySelector('[data-ticket-messages]');
    const status = thread.querySelector('[data-ticket-status]');
    const reply = thread.querySelector('[data-ticket-reply]');
    let last = Number(messages.lastElementChild?.dataset.messageId ?? 0);

    async function refresh() {
        try {
            if (document.hidden) return;
            const url = new URL(thread.dataset.ticketUrl);
            url.searchParams.set('after', last);
            const response = await fetch(url, {headers: {Accept: 'application/json'}, cache: 'no-store'});
            if (!response.ok) return;

            const data = await response.json();
            status.textContent = data.status;
            reply.hidden = data.closed;
            for (const message of data.messages) {
                const item = document.createElement('li');
                item.classList.toggle('support-agent', Boolean(Number(message.from_support)));
                const author = document.createElement('strong');
                author.textContent = message.author_name ? `${message.author_role_label}: ${message.author_name}` : message.author_role_label;
                const heading = document.createElement('div');
                heading.className = 'support-message-heading';
                const avatar = document.createElement('span');
                avatar.className = 'support-avatar';
                avatar.setAttribute('aria-hidden', 'true');
                avatar.textContent = Array.from(message.author_name || message.author_role_label)[0];
                if (message.avatar) {
                    const image = document.createElement('img');
                    image.alt = '';
                    image.addEventListener('error', () => image.remove());
                    image.src = message.avatar;
                    avatar.append(image);
                }
                const time = document.createElement('time');
                time.textContent = message.created_at;
                const body = document.createElement('p');
                body.textContent = message.body;
                heading.append(avatar, author, time);
                item.append(heading, body);
                messages.append(item);
                last = message.id;
            }
        } catch {
            // A later poll can recover from a temporary network error.
        } finally {
            setTimeout(refresh, 4000);
        }
    }
    setTimeout(refresh, 4000);
}
