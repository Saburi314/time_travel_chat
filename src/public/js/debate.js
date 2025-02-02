document.addEventListener('DOMContentLoaded', async () => {
    const tokenElement = document.querySelector('meta[name="csrf-token"]');
    let token = tokenElement ? tokenElement.getAttribute('content') : '';

    const form = document.getElementById('chat-form');
    const input = document.getElementById('user-input');
    const chatArea = document.getElementById('chat-area');
    const resetButton = document.getElementById('reset-button');

    /**
     *  チャット履歴を取得
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
     *  ユーザーのメッセージを送信
     */
    async function sendUserMessage(userMessage) {
        if (!userMessage) return;

        addMessage('user', userMessage);
        input.value = '';

        //  AIのレスポンス待ちを表示
        const loadingMessage = showLoadingMessage();

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
            removeLoadingMessage(loadingMessage);
            addMessage('assistant', data.response || 'エラーが発生しました。');
        } catch (error) {
            console.error('エラー:', error);
            removeLoadingMessage(loadingMessage);
            chatArea.innerHTML += `<div class="text-danger">❌ AIとの通信でエラーが発生しました。</div>`;
        }
    }

    /**
     *  チャットをリセット
     */
    async function resetChatSession() {
        //  リセット中のスピナーを表示
        const loadingMessage = showLoadingMessage('ディベートをリセット中...');

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
        } finally {
            removeLoadingMessage(loadingMessage);
        }
    }

    /**
     *  CSRFトークンを更新
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
     *  チャットメッセージを追加
     */
    function addMessage(role, content) {
        const roleMap = {
            'user': 'あなた',
            'assistant': 'ひろゆき'
        };
        const displayRole = roleMap[role] || role;
        const roleClass = role === 'user' ? 'user' : 'ai';

        //  メッセージ全体のコンテナ
        const messageRow = document.createElement('div');
        messageRow.classList.add('message-row', roleClass);

        //  AIのときだけアイコンを表示
        if (role === 'assistant') {
            const icon = document.createElement('img');
            icon.classList.add('ai-icon');
            icon.src = '/images/hiroyuki_icon.webp';
            icon.alt = 'ひろゆき';
            messageRow.appendChild(icon);
        }

        //  メッセージ吹き出し
        const messageBubble = document.createElement('div');
        messageBubble.classList.add('bubble', roleClass);
        
        //  "結果発表" が含まれる場合、スタイルを変更
        if (content.includes("結果発表")) {
            messageBubble.innerHTML = `<strong class="result-heading">${content}</strong>`;
        } else {
            messageBubble.textContent = content;
        }

        messageRow.appendChild(messageBubble);
        chatArea.appendChild(messageRow);
        chatArea.scrollTop = chatArea.scrollHeight;
    }

    /**
     *  読み込み中のスピナーを表示
     */
    function showLoadingMessage(text = "考え中...") {
        const messageRow = document.createElement('div');
        messageRow.classList.add('message-row', 'ai');

        const messageBubble = document.createElement('div');
        messageBubble.classList.add('bubble', 'ai');
        messageBubble.innerHTML = `<span class="loading-spinner"></span> ${text}`;

        messageRow.appendChild(messageBubble);
        chatArea.appendChild(messageRow);
        chatArea.scrollTop = chatArea.scrollHeight;

        return messageRow;
    }

    /**
     *  読み込み中のスピナーを削除
     */
    function removeLoadingMessage(messageRow) {
        if (messageRow && messageRow.parentNode === chatArea) {
            chatArea.removeChild(messageRow);
        }
    }

    // 初回の履歴読み込み
    await loadChatHistory();

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        await sendUserMessage(input.value.trim());
    });

    resetButton.addEventListener('click', async () => {
        await resetChatSession();
    });
});
