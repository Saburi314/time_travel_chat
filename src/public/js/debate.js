document.addEventListener('DOMContentLoaded', () => {    
    initializeChatApp();
});

const opponent = window.opponent; // Laravel から受け取る opponent 情報
const opponentId = opponent.id;

/**
 * 🔹 チャットアプリの初期化
 */
async function initializeChatApp() {
    const form = document.getElementById('chat-form');
    const input = document.getElementById('user-input');
    const chatArea = document.getElementById('chat-area');
    const sendButton = document.getElementById('send-button');
    const resetButton = document.getElementById('reset-button');
    const initialMessage = '';

    // ボタンを一時的に無効化
    setButtonsDisabled(true, sendButton, resetButton);

    // 履歴を取得し、画面に反映
    const hasHistory = await loadChatHistory(chatArea);

    // 履歴がない場合、AI が最初に発言
    if (!hasHistory) {
        await sendUserMessage(initialMessage, chatArea, input, true);
    }

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
    resetButton.addEventListener('click', async () => await resetChat(chatArea));
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
        const response = await fetch(`/api/get-chat-history?opponentId=${opponentId}`, { method: 'GET', credentials: 'include' });
        if (!response.ok) throw new Error(`履歴取得エラー: ${response.status}`);

        const data = await response.json();

        // data.data.historyを参照するように修正
        if (data.data && data.data.history && data.data.history.length > 0) {
            data.data.history.forEach(({ role, content }) => addMessage(role, content, chatArea));
            return true; // 履歴が存在する
        }
    } catch (error) {
        console.error('履歴取得エラー:', error);
    }
    return false; // 履歴がない
}

/**
 * 🔹 ユーザーのメッセージを送信
 */
async function sendUserMessage(userMessage, chatArea, input, isInitialAiMessage = false) {
    if (!isInitialAiMessage && !userMessage) return;

    const sendButton = document.getElementById('send-button');
    const resetButton = document.getElementById('reset-button');
    setButtonsDisabled(true, sendButton, resetButton); // 両方のボタンを無効化

    if (!isInitialAiMessage) {
        addMessage('user', userMessage, chatArea);
        input.value = '';
    }

    const loadingMessage = showLoadingMessage(chatArea);

    try {
        const requestData = {
            opponentId,
            message: userMessage
        };

        console.log("📤 AIリクエスト送信:", requestData);

        const response = await fetch('/api/ai-response', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestData)
        });

        console.log("📥 AIレスポンス取得:", response);

        if (!response.ok) {
            throw new Error(`HTTPエラー: ${response.status}`);
        }

        const data = await response.json();

        removeLoadingMessage(loadingMessage, chatArea);

        if (!data.data || !data.data.response) {
            console.error("❌ AIレスポンスが空です:", data);
            addMessage('assistant', '❌ AIからの返答が取得できませんでした。', chatArea);
            return;
        }

        addMessage('assistant', data.data.response, chatArea);
    } catch (error) {
        console.error("❌ Fetch エラー:", error);
        removeLoadingMessage(loadingMessage, chatArea);
        chatArea.innerHTML += `<div class="text-danger">❌ AIとの通信でエラーが発生しました。</div>`;
    } finally {
        setButtonsDisabled(false, sendButton, resetButton); // 両方のボタンを再び有効化
    }
}

/**
 * 🔹 チャットをリセット
 */
async function resetChat(chatArea) {
    const sendButton = document.getElementById('send-button');
    const resetButton = document.getElementById('reset-button');
    setButtonsDisabled(true, sendButton, resetButton); // 両方のボタンを無効化

    // リセット中のスピナーを表示
    const loadingMessage = showLoadingMessage(chatArea, 'ディベートをリセット中...');

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const response = await fetch('/api/delete-chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                opponentId: opponentId // opponentIdをリクエストボディに含める
            })
        });

        if (!response.ok) {
            throw new Error(`HTTPエラー: ${response.status}`);
        }

        const data = await response.json();
        chatArea.innerHTML = '<div class="text-success">ディベートの履歴をリセットしました。AIの記憶もリセットされました。</div>';

        // リセット後に AI の最初の発言を表示
        await sendUserMessage('', chatArea, null, true);
    } catch (error) {
        handleFetchError(error, chatArea, loadingMessage);
    } finally {
        setButtonsDisabled(false, sendButton, resetButton); // 両方のボタンを再び有効化
    }
}

/**
 * 🔹 Fetch エラーの処理
 */
function handleFetchError(error, chatArea, loadingMessage) {
    console.error('❌ Fetch エラー:', error);  // 🔹 エラーをコンソールに出力
    console.error('❌ エラーレスポンス:', error.response);

    removeLoadingMessage(loadingMessage, chatArea);
    chatArea.innerHTML += `<div class="text-danger">❌ AIとの通信でエラーが発生しました。</div>`;
}

/**
 * 🔹 チャットメッセージを追加
 */
function addMessage(role, content, chatArea) {
    // contentが空白またはundefinedの場合は何もしない
    if (!content || content.trim() === '') {
        console.log(`Skipping empty message for role=${role}`);
        return;
    }

    console.log(`Adding message: role=${role}, content=${content}`); // デバッグ情報を追加

    const messageRow = document.createElement('div');
    const roleClass = role === 'assistant' ? 'ai' : 'user';
    messageRow.classList.add('message-row', roleClass);

    if (roleClass === 'ai') {
        const icon = document.createElement('img');
        icon.classList.add('ai-icon');
        icon.src = opponent.image;
        icon.alt = opponent.name;
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
function showLoadingMessage(chatArea, text = "タイムトラベル中...") {
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
