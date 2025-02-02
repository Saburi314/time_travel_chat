document.addEventListener('DOMContentLoaded', async () => {
    const tokenElement = document.querySelector('meta[name="csrf-token"]');
    const token = tokenElement ? tokenElement.getAttribute('content') : '';

    const form = document.getElementById('chat-form');
    const input = document.getElementById('user-input');
    const chatArea = document.getElementById('chat-area');
    const resetButton = document.getElementById('reset-button');

    async function loadChatHistory() {
        try {
            const response = await fetch('/get-chat-history', { method: 'GET', credentials: 'include' });
            const data = await response.json();
            if (data.history) {
                data.history.forEach(({ role, content }) => addMessage(role, content));
            }
        } catch (error) {
            console.error('履歴取得エラー:', error);
        }
    }

    await loadChatHistory();

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const userMessage = input.value.trim();
        if (!userMessage) return;

        addMessage('user', userMessage);
        input.value = '';

        try {
            const response = await fetch('/ai-response', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                credentials: 'include',
                body: JSON.stringify({ message: userMessage })
            });
            const data = await response.json();
            addMessage('assistant', data.response || 'エラーが発生しました。');
        } catch (error) {
            console.error('エラー:', error);
        }
    });

    resetButton.addEventListener('click', async () => {
        await fetch('/reset-chat', { method: 'POST', headers: { 'X-CSRF-TOKEN': token }, credentials: 'include' });
        chatArea.innerHTML = '';
    });

    function addMessage(role, content) {
        const messageDiv = document.createElement('div');
        messageDiv.innerHTML = `<strong>${role}:</strong> ${content}`;
        chatArea.appendChild(messageDiv);
        chatArea.scrollTop = chatArea.scrollHeight;
    }
});
