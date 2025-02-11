document.addEventListener('DOMContentLoaded', () => {    
    initializeChatApp();
});

const opponentKey = window.opponentKey || 'hiroyuki';
const Opponents = window.Opponents;
const opponentData = Opponents[opponentKey] || Opponents['hiroyuki'];

/**
 * 🔹 チャットアプリの初期化
 */
async function initializeChatApp() {
    const form = document.getElementById('chat-form');
    const input = document.getElementById('user-input');
    const chatArea = document.getElementById('chat-area');
    const resetButton = document.getElementById('reset-button');

    await loadChatHistory(chatArea);
    registerEventListeners(form, input, resetButton, chatArea);
}

/**
 * 🔹 イベントリスナーの登録
 */
function registerEventListeners(form, input, resetButton, chatArea) {
    input.addEventListener('keydown', (event) => handleUserInputKeydown(event, form));
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        await sendUserMessage(input.value.trim(), chatArea, input);
    });
    resetButton.addEventListener('click', async () => {
        await resetChatSession(chatArea);
    });
}

/**
 * 🔹 `Shift+Enter` で改行、`Enter` で送信
 */
function handleUserInputKeydown(event, form) {
    if (event.key === 'Enter') {
        if (event.shiftKey) {
            event.preventDefault();
            insertNewLineAtCursor(event.target);
        } else {
            event.preventDefault();
            form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        }
    }
}

/**
 * 🔹 チャット履歴を取得
 */
async function loadChatHistory(chatArea) {
    try {
        const response = await fetch(`/get-chat-history?opponentKey=${opponentKey}`, { method: 'GET', credentials: 'include' });
        if (!response.ok) throw new Error(`履歴取得エラー: ${response.status}`);

        const data = await response.json();
        if (data.history) {
            data.history.forEach(({ role, content }) => addMessage(role, content, chatArea));
        }
    } catch (error) {
        console.error('履歴取得エラー:', error);
    }
}

/**
 * 🔹 ユーザーのメッセージを送信
 */
async function sendUserMessage(userMessage, chatArea, input) {
    if (!userMessage) return;

    addMessage('user', userMessage, chatArea);
    input.value = '';

    //  AIのレスポンス待ちを表示
    const loadingMessage = showLoadingMessage(chatArea);

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const response = await fetch('/ai-response', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            credentials: 'include',
            body: JSON.stringify({ message: userMessage, opponentKey: opponentKey })
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`送信エラー: ${response.status} - ${errorText}`);
        }

        const data = await response.json();
        removeLoadingMessage(loadingMessage, chatArea);
        addMessage('assistant', data.response || 'エラーが発生しました。', chatArea);
    } catch (error) {
        console.error('エラー:', error);
        removeLoadingMessage(loadingMessage, chatArea);
        chatArea.innerHTML += `<div class="text-danger">❌ AIとの通信でエラーが発生しました。</div>`;
    }
}

/**
 * 🔹 チャットをリセット
 */
async function resetChatSession(chatArea) {
    //  リセット中のスピナーを表示
    const loadingMessage = showLoadingMessage(chatArea, 'ディベートをリセット中...');

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const response = await fetch('/reset-chat', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            credentials: 'include',
        });

        if (!response.ok) throw new Error(`リセットエラー: ${response.status}`);

        const data = await response.json();
        updateCsrfToken(data.csrf_token);
        chatArea.innerHTML = '<div class="text-success">ディベートの履歴をリセットしました。AIの記憶もリセットされました。</div>';
    } catch (error) {
        console.error('リセットエラー:', error);
        chatArea.innerHTML = '<div class="text-danger">履歴のリセットに失敗しました。</div>';
    } finally {
        removeLoadingMessage(loadingMessage, chatArea);
    }
}

/**
 * 🔹 CSRFトークンを更新
 */
function updateCsrfToken(newToken) {
    if (newToken) {
        document.querySelector('meta[name="csrf-token"]').setAttribute('content', newToken);
    } else {
        console.warn("CSRF トークンがレスポンスに含まれていません。");
    }
}

/**
 * 🔹 チャットメッセージを追加
 */
function addMessage(role, content, chatArea) {
    const roleClass = role === 'user' ? 'user' : 'ai';

    //  メッセージ全体のコンテナ
    const messageRow = document.createElement('div');
    messageRow.classList.add('message-row', roleClass);

    //  AIのときだけアイコンを表示
    if (role === 'assistant') {
        const icon = document.createElement('img');
        icon.classList.add('ai-icon');
        icon.src = opponentData.image;
        icon.alt = opponentData.name;
        messageRow.appendChild(icon);
    }

    //  メッセージ吹き出し
    const messageBubble = document.createElement('div');
    messageBubble.classList.add('bubble', roleClass);

    // `###` の行を見出しとして処理
    const lines = content.split("\n");
    messageBubble.innerHTML = lines
        .map(line => line.startsWith("### ") ? `<h3 class="result-heading">${line.replace('### ', '')}</h3>` : `<p>${line}</p>`)
        .join("");

    messageRow.appendChild(messageBubble);
    chatArea.appendChild(messageRow);
    chatArea.scrollTop = chatArea.scrollHeight;
}

/**
 * 🔹 読み込み中のスピナーを表示
 */
function showLoadingMessage(chatArea, text = "考え中...") {
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
 * 🔹 読み込み中のスピナーを削除
 */
function removeLoadingMessage(messageRow, chatArea) {
    if (messageRow && messageRow.parentNode === chatArea) {
        chatArea.removeChild(messageRow);
    }
}

/**
 * 🔹 カーソル位置に改行を挿入
 */
function insertNewLineAtCursor(textarea) {
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const value = textarea.value;

    textarea.value = value.substring(0, start) + "\n" + value.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + 1;
}
