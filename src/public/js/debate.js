document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('chat-form');
    const input = document.getElementById('user-input');
    const chatArea = document.getElementById('chat-area');
    const resetButton = document.getElementById('reset-button'); // リセットボタンの取得

    // メッセージ送信処理
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const userMessage = input.value.trim();
        if (userMessage) {
            addMessage('user', userMessage);
            input.value = '';

            try {
                const response = await fetch('/api/ai-response', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ message: userMessage }),
                });

                const data = await response.json();
                const aiMessage = data.response || 'エラーが発生しました。';
                addMessage('ai', aiMessage);
            } catch (error) {
                console.error('エラー:', error);
                addMessage('ai', 'エラーが発生しました。');
            }
        }
    });

    // リセットボタン処理
    resetButton.addEventListener('click', async () => {
        try {
            const response = await fetch('/api/reset-chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            if (response.ok) {
                chatArea.innerHTML = ''; // チャットエリアをクリア
                alert('ディベートの内容をリセットしました。');
            } else {
                alert('ディベートの内容をリセット出来ませんでした。');
            }
        } catch (error) {
            console.error('リセットエラー:', error);
            alert('サーバーエラーが発生しました。');
        }
    });

    function addMessage(sender, message) {
        const messageRow = document.createElement('div');
        messageRow.classList.add('message-row', sender);

        if (sender === 'ai') {
            const aiIcon = document.createElement('img');
            aiIcon.src = '/images/hiroyuki_icon.webp'; // アイコン画像のパス
            aiIcon.alt = 'AIアイコン';
            aiIcon.classList.add('ai-icon');
            messageRow.appendChild(aiIcon);
        }

        const bubble = document.createElement('div');
        bubble.classList.add('bubble', sender);
        bubble.textContent = message;

        messageRow.appendChild(bubble);
        chatArea.appendChild(messageRow);
        chatArea.scrollTop = chatArea.scrollHeight;
    }
});
