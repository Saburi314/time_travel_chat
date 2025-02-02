document.addEventListener('DOMContentLoaded', async () => {
    const tokenElement = document.querySelector('meta[name="csrf-token"]');
    let token = tokenElement ? tokenElement.getAttribute('content') : '';

    const form = document.getElementById('chat-form');
    const input = document.getElementById('user-input');
    const chatArea = document.getElementById('chat-area');
    const resetButton = document.getElementById('reset-button');

    /**
     * 🔹 チャット履歴を取得
     */
    async function loadChatHistory() {
        try {
            const response = await fetch('/get-chat-history', { method: 'GET', credentials: 'include' });
            if (!response.ok) throw new Error(`履歴取得エラー: ${response.status}`);

            const data = await response.json();
            if (data.history) {
                data.history.forEach(({ role, content }) => addMessage(role, content));
            }
        } catch (error) {
            console.error('履歴取得エラー:', error);
        }
    }

    /**
     * 🔹 ユーザーのメッセージを送信
     */
    async function sendUserMessage(userMessage) {
        if (!userMessage) return;

        addMessage('あなた', userMessage);
        input.value = '';

        try {
            const response = await fetch('/ai-response', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                credentials: 'include',
                body: JSON.stringify({ message: userMessage })
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`送信エラー: ${response.status} - ${errorText}`);
            }

            const data = await response.json();
            addMessage('ひろゆき', data.response || 'エラーが発生しました。');
        } catch (error) {
            console.error('エラー:', error);
            chatArea.innerHTML += `<div class="text-danger">❌ AIとの通信でエラーが発生しました。</div>`;
        }
    }

    /**
     * 🔹 チャットをリセット
     */
    async function resetChatSession() {
        try {
            const response = await fetch('/reset-chat', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token },
                credentials: 'include'
            });

            if (!response.ok) throw new Error(`リセットエラー: ${response.status}`);

            const data = await response.json();
            updateCsrfToken(data.csrf_token);
            chatArea.innerHTML = '<div class="text-success">ディベートの履歴をリセットしました。AIの記憶もリセットされました。</div>';
        } catch (error) {
            console.error('リセットエラー:', error);
            chatArea.innerHTML = '<div class="text-danger">履歴のリセットに失敗しました。</div>';
        }
    }

    /**
     * 🔹 CSRFトークンを更新
     */
    function updateCsrfToken(newToken) {
        if (newToken) {
            token = newToken;
            document.querySelector('meta[name="csrf-token"]').setAttribute('content', token);
        } else {
            console.warn("⚠ CSRF トークンがレスポンスに含まれていません。");
        }
    }

    /**
     * 🔹 チャットメッセージを追加
     */
    function addMessage(role, content) {
        const messageDiv = document.createElement('div');
        messageDiv.innerHTML = `<strong>${role}:</strong> ${content}`;
        chatArea.appendChild(messageDiv);
        chatArea.scrollTop = chatArea.scrollHeight;
    }

    // 🔹 初回の履歴読み込み
    await loadChatHistory();

    // 🔹 ユーザーからメッセージが送信された場合
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        await sendUserMessage(input.value.trim());
    });

    // 🔹 リセットボタンがクリックされた場合
    resetButton.addEventListener('click', async () => {
        await resetChatSession();
    });
});
