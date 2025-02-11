document.addEventListener('DOMContentLoaded', () => {    
    initializeChatApp();
});

let opponentKey = window.opponentKey;
let Opponents = window.Opponents;
let opponentData = Opponents[opponentKey];

/**
 * 🔹 チャットアプリの初期化
 */
async function initializeChatApp() {
    const form = document.getElementById('chat-form');
    const input = document.getElementById('user-input');
    const chatArea = document.getElementById('chat-area');
    const sendButton = document.getElementById('send-button');
    const resetButton = document.getElementById('reset-button');

    // ボタンを一時的に無効化
    setButtonsDisabled(true, sendButton, resetButton);

    // 履歴を取得し、画面に反映
    await loadChatHistory(chatArea);

    // **最初に AI が話す**
    await sendUserMessage('', chatArea, input, true);

    // イベントリスナーを登録
    registerEventListeners(form, input, resetButton, chatArea);

    // 初期化完了後にボタンを有効化
    setButtonsDisabled(false, sendButton, resetButton);
}

/**
 * 🔹 ボタンの有効・無効を切り替え
 */
function setButtonsDisabled(disabled, ...buttons) {
    buttons.forEach(button => {
        if (button) button.disabled = disabled;
    });
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
    resetButton.addEventListener('click', async () => await resetChatSession(chatArea));
}

/**
 * 🔹 `Shift+Enter` で改行、`Enter` で送信
 */
function handleUserInputKeydown(event, form) {
    if (event.key === 'Enter') {
        event.preventDefault();
        event.shiftKey ? insertNewLineAtCursor(event.target) : form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
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
        data.history?.forEach(({ role, content }) => addMessage(role, content, chatArea));
    } catch (error) {
        console.error('履歴取得エラー:', error);
    }
}

/**
 * 🔹 ユーザーのメッセージを送信
 */
async function sendUserMessage(userMessage, chatArea, input, isInitialAiMessage = false) {
    if (!isInitialAiMessage && !userMessage) return;

    if (!isInitialAiMessage) {
        addMessage('user', userMessage, chatArea);
        input.value = '';
    }

    //  AIのレスポンス待ちを表示
    const loadingMessage = showLoadingMessage(chatArea);

    try {
        const bodyData = isInitialAiMessage ? { opponentKey } : { message: userMessage, opponentKey };
        const response = await fetchJson('/ai-response', 'POST', bodyData);

        removeLoadingMessage(loadingMessage, chatArea);
        addMessage('assistant', response.response || 'エラーが発生しました。', chatArea);
    } catch (error) {
        handleFetchError(error, chatArea, loadingMessage);
    }
}

/**
 * 🔹 チャットをリセット
 */
async function resetChatSession(chatArea) {
    // リセット中のスピナーを表示
    const loadingMessage = showLoadingMessage(chatArea, 'ディベートをリセット中...');

    try {
        const response = await fetchJson('/reset-chat', 'POST');
        updateCsrfToken(response.csrf_token);
        chatArea.innerHTML = '<div class="text-success">ディベートの履歴をリセットしました。AIの記憶もリセットされました。</div>';
    } catch (error) {
        handleFetchError(error, chatArea, loadingMessage);
    }
}

/**
 * 🔹 Fetch API の共通ラッパー
 */
async function fetchJson(url, method = 'GET', body = null) {
    const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() };
    const options = { method, headers, credentials: 'include' };

    if (body) options.body = JSON.stringify(body);

    const response = await fetch(url, options);
    if (!response.ok) throw new Error(`HTTPエラー: ${response.status}`);

    return response.json();
}

/**
 * 🔹 Fetch エラーの処理
 */
function handleFetchError(error, chatArea, loadingMessage) {
    console.error('エラー:', error);
    removeLoadingMessage(loadingMessage, chatArea);
    chatArea.innerHTML += `<div class="text-danger">❌ AIとの通信でエラーが発生しました。</div>`;
}

/**
 * 🔹 CSRFトークンを取得
 */
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
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
    const messageRow = document.createElement('div');
    const roleClass = role === 'assistant' ? 'ai' : 'user';
    messageRow.classList.add('message-row', roleClass);

    if (roleClass === 'ai') {
        const icon = document.createElement('img');
        icon.classList.add('ai-icon');
        icon.src = opponentData.image;
        icon.alt = opponentData.name;
        messageRow.appendChild(icon);
    }

    const messageBubble = document.createElement('div');
    messageBubble.classList.add('bubble', roleClass);
    messageBubble.innerHTML = formatMessageContent(content);

    messageRow.appendChild(messageBubble);
    chatArea.appendChild(messageRow);
    chatArea.scrollTop = chatArea.scrollHeight;
}


/**
 * 🔹 メッセージ内容を整形
 */
function formatMessageContent(content) {
    return content.split("\n").map(line => 
        line.startsWith("### ") ? `<h3 class="result-heading">${line.replace('### ', '')}</h3>` : `<p>${line}</p>`
    ).join("");
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
    if (messageRow?.parentNode === chatArea) {
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
