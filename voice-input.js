window.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

const recognition = new SpeechRecognition();
recognition.lang = 'uk-UA';  // Встановлюємо мову на українську
recognition.interimResults = false;

const startBtn = document.getElementById('start-record-btn');
const transcriptArea = document.getElementById('transcript');
const status = document.getElementById('status');

startBtn.addEventListener('click', () => {
    recognition.start();
    status.textContent = 'Слухаю...';
});

recognition.addEventListener('result', (event) => {
    const transcript = event.results[0][0].transcript;
    transcriptArea.value += transcript + '\n';
});

recognition.addEventListener('end', () => {
    status.textContent = 'Натисніть, щоб почати говорити';
});
