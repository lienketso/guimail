document.addEventListener('DOMContentLoaded', function() {
    const widget = document.getElementById('chatbot-widget');
    const toggleBtn = document.getElementById('chatbot-toggle');
    const showBtn = document.getElementById('chatbot-show-btn');
    const input = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send');
    const messages = document.getElementById('chatbot-messages');

    toggleBtn.onclick = function() {
        widget.style.display = 'none';
        showBtn.style.display = 'block';
    };
    showBtn.onclick = function() {
        widget.style.display = 'block';
        showBtn.style.display = 'none';
    };

    sendBtn.onclick = sendMessage;
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') sendMessage();
    });

    function sendMessage() {
        const text = input.value.trim();
        if (!text) return;
        appendMessage('Bạn', text);
        input.value = '';
        // Gửi API thật
        fetch('/public/api/chatbot', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message: text })
        })
        .then(res => res.json())
        .then(data => {
            appendMessage('Chatbot', data.answer);
        })
        .catch(() => {
            appendMessage('Chatbot', 'Có lỗi xảy ra, vui lòng thử lại sau.');
        });
    }

    function appendMessage(sender, text) {
        const msg = document.createElement('div');
        msg.innerHTML = `<strong>${sender}:</strong> ${text}`;
        messages.appendChild(msg);
        messages.scrollTop = messages.scrollHeight;
    }
});
