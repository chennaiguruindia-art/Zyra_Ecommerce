{{-- ZYRA Shop Assistant Widget (rule-based chatbot) --}}
<div id="zyraAiChat">
    <button type="button" id="zyraAiChatToggle" class="zyra-ai-toggle" aria-label="Chat with the ZYRA assistant" aria-expanded="false">
        <i class="bi bi-chat-dots-fill"></i>
    </button>

    <div id="zyraAiChatPanel" class="zyra-ai-panel" hidden>
        <div class="zyra-ai-header">
            <div class="zyra-ai-brand">
                <span class="zyra-ai-avatar">Z</span>
                <div>
                    <strong>ZYRA Assistant</strong>
                    <small><span class="zyra-ai-dot"></span> Online — replies instantly</small>
                </div>
            </div>
            <button type="button" id="zyraAiChatClose" class="zyra-ai-close" aria-label="Close chat">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="zyraAiChatMessages" class="zyra-ai-messages"></div>

        <div id="zyraAiChatSuggestions" class="zyra-ai-suggestions">
            <button type="button" data-q="Where is my order?">Where is my order?</button>
            <button type="button" data-q="Show kurtis">Show kurtis</button>
            <button type="button" data-q="Any offers?">Any offers?</button>
            <button type="button" data-q="What is your return policy?">Return policy?</button>
        </div>

        <form id="zyraAiChatForm" class="zyra-ai-form" autocomplete="off">
            <input id="zyraAiChatInput" type="text" maxlength="500" placeholder="Type your question…" />
            <button type="submit" id="zyraAiChatSend" aria-label="Send message"><i class="bi bi-send-fill"></i></button>
        </form>
        <small class="zyra-ai-footnote">Tip: <a href="/login">log in</a> so I can fetch your live order status.</small>
    </div>
</div>

<style>
#zyraAiChat { position: fixed; right: 20px; bottom: 20px; z-index: 9999; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
.zyra-ai-toggle { width: 58px; height: 58px; border-radius: 50%; border: none; background: #18181b; color: #fff; font-size: 1.4rem; box-shadow: 0 12px 30px rgba(0,0,0,.25); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform .15s ease; }
.zyra-ai-toggle:hover { transform: scale(1.07); }
.zyra-ai-panel { position: fixed; right: 20px; bottom: 92px; width: 360px; max-width: calc(100vw - 40px); max-height: calc(100dvh - 120px); background: #fff; border-radius: 18px; box-shadow: 0 20px 60px rgba(0,0,0,.22); overflow: hidden; display: flex; flex-direction: column; }
.zyra-ai-panel[hidden] { display: none; }
.zyra-ai-header { display: flex; align-items: center; justify-content: space-between; background: #18181b; color: #fff; padding: 13px 15px; }
.zyra-ai-brand { display: flex; align-items: center; gap: 10px; }
.zyra-ai-avatar { width: 38px; height: 38px; border-radius: 50%; background: #8d5a5a; color: #fff; font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1.15rem; display: inline-flex; align-items: center; justify-content: center; }
.zyra-ai-brand strong { font-size: .95rem; display: block; line-height: 1.2; }
.zyra-ai-brand small { font-size: .72rem; opacity: .85; }
.zyra-ai-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #34d399; margin-right: 4px; }
.zyra-ai-close { background: none; border: none; color: #fff; font-size: 1.05rem; opacity: .8; cursor: pointer; }
.zyra-ai-close:hover { opacity: 1; }
.zyra-ai-messages { height: 320px; max-height: calc(100dvh - 420px); min-height: 160px; overflow-y: auto; padding: 14px; display: flex; flex-direction: column; gap: 10px; background: #faf8f6; }
.zyra-ai-msg { max-width: 82%; padding: 9px 12px; border-radius: 14px; font-size: .875rem; line-height: 1.45; word-break: break-word; }
.zyra-ai-msg.user { align-self: flex-end; background: #18181b; color: #fff; border-bottom-right-radius: 4px; white-space: pre-wrap; }
.zyra-ai-msg.bot { align-self: flex-start; background: #fff; color: #2b2323; border: 1px solid #ece5e0; border-bottom-left-radius: 4px; }
.zyra-ai-msg.bot a { color: #8d5a5a; font-weight: 600; }
.zyra-ai-suggestions { display: flex; flex-wrap: wrap; gap: 6px; padding: 10px 12px 2px; background: #faf8f6; }
.zyra-ai-suggestions button { font-size: .74rem; border: 1px solid #e0d6cf; background: #fff; color: #5a4a4a; border-radius: 30px; padding: 4px 11px; cursor: pointer; }
.zyra-ai-suggestions button:hover { border-color: #8d5a5a; color: #8d5a5a; }
.zyra-ai-form { display: flex; gap: 8px; padding: 10px 12px; background: #fff; }
.zyra-ai-form input { flex: 1; border: 1px solid #e0d6cf; border-radius: 30px; padding: 9px 14px; font-size: .875rem; outline: none; }
.zyra-ai-form input:focus { border-color: #8d5a5a; }
.zyra-ai-form button { width: 40px; height: 40px; border: none; border-radius: 50%; background: #8d5a5a; color: #fff; font-size: .95rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
.zyra-ai-form button:disabled { opacity: .5; cursor: not-allowed; }
.zyra-ai-footnote { display: block; text-align: center; font-size: .68rem; color: #a99; padding: 0 12px 10px; background: #fff; }
.zyra-ai-footnote a { color: #8d5a5a; font-weight: 600; }
</style>

<script src="{{ asset('js/chatbot.js') }}?v=20260923a"></script>