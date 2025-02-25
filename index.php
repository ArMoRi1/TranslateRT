<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>AI Translate with Azure</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        div {
            margin-bottom: 20px;
        }
        textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px 15px;
            text-align: center;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
        }
        button:hover {
            background-color: #45a049;
        }
        h2 {
            margin-top: 30px;
            color: #333;
        }
        #status {
            font-style: italic;
            color: #666;
        }
        .api-settings {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        .api-key-input {
            margin-top: 10px;
        }
    </style>
</head>
<body>

<!-- Language selection -->
<div>
    <div>
        <label for="sourceLanguage">Source Language:</label>
        <select id="sourceLanguage">
            <option value="en">English</option>
            <option value="uk">Ukrainian</option>
            <option value="es">Spanish</option>
            <option value="fr">French</option>
            <option value="de">German</option>
            <option value="it">Italian</option>
            <option value="ru">Russian</option>
            <option value="ja">Japanese</option>
            <option value="zh-Hans">Chinese (Simplified)</option>
            <option value="pl">Polish</option>
            <option value="tr">Turkish</option>
            <option value="ar">Arabic</option>
            <option value="nl">Dutch</option>
            <option value="pt">Portuguese</option>
            <option value="ko">Korean</option>
            <option value="sv">Swedish</option>
            <option value="hi">Hindi</option>
            <option value="el">Greek</option>
        </select>
    </div>

    <div>
        <label for="targetLanguage">Target Language:</label>
        <select id="targetLanguage">
            <option value="en">English</option>
            <option value="uk">Ukrainian</option>
            <option value="es">Spanish</option>
            <option value="fr">French</option>
            <option value="de">German</option>
            <option value="it">Italian</option>
            <option value="ru">Russian</option>
            <option value="ja">Japanese</option>
            <option value="zh-Hans">Chinese (Simplified)</option>
            <option value="pl">Polish</option>
            <option value="tr">Turkish</option>
            <option value="ar">Arabic</option>
            <option value="nl">Dutch</option>
            <option value="pt">Portuguese</option>
            <option value="ko">Korean</option>
            <option value="sv">Swedish</option>
            <option value="hi">Hindi</option>
            <option value="el">Greek</option>
        </select>
    </div>
</div>

<!-- Voice Input -->
<div>
    <h2>Voice Input</h2>
    <button id="start-record-btn">Start Recording</button>
    <p id="status">Press to start speaking</p>
    <textarea id="transcript" rows="5" cols="40" placeholder="Your speech will appear here..."></textarea>
</div>

<!-- AI Translation -->
<div>
    <h2>Translation</h2>
    <p>Azure Translator translates the source language to the target language based on your input</p>
    <textarea id="translatedText" rows="5" cols="40" placeholder="Translated text will appear here..."></textarea>
</div>

<!-- Text-to-Speech Output -->
<div>
    <h2>Speech Output</h2>
    <button id="play-translation-btn">Play Translation</button>
</div>

<!-- Scripts for voice input and text-to-speech -->
<script>
    const startRecordBtn = document.getElementById('start-record-btn');
    const statusDisplay = document.getElementById('status');
    const transcriptDisplay = document.getElementById('transcript');
    const sourceLanguageSelect = document.getElementById('sourceLanguage');
    const targetLanguageSelect = document.getElementById('targetLanguage');

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();
    let recognitionStarted = false;

    startRecordBtn.addEventListener('click', () => {
        if (!recognitionStarted) {
            const sourceLanguageCode = sourceLanguageSelect.value;
            recognition.lang = sourceLanguageCode;
            console.log(`Setting recognition language to: ${sourceLanguageCode}`);
            recognition.start();
            recognitionStarted = true;
            statusDisplay.textContent = "Recording...";
        } else {
            statusDisplay.textContent = "Recording is already active.";
        }
    });

    recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        transcriptDisplay.value = transcript;
        statusDisplay.textContent = "Speech captured!";
        translateText();
    };

    recognition.onspeechend = () => {
        recognition.stop();
        recognitionStarted = false;
        statusDisplay.textContent = "Recording stopped.";
    };

    recognition.onerror = (event) => {
        console.error('Speech recognition error:', event.error);
        recognitionStarted = false;
        statusDisplay.textContent = `Error: ${event.error}`;
    };

    const translateText = async () => {
        const transcript = document.getElementById('transcript').value;
        const translatedTextArea = document.getElementById('translatedText');
        const azureKey = '4ufZlJWIvPmxmtzSoV0KylqgtODrktGEsL4hZmlNUvoA6kZsafRNJQQJ99BBACYeBjFXJ3w3AAAbACOG2YwF';
        const azureRegion = 'eastus';

        if (!transcript.trim()) {
            console.log("No text to translate");
            return;
        }

        const sourceLanguage = sourceLanguageSelect.value;
        const targetLanguage = targetLanguageSelect.value;

        console.log(`Translating from ${sourceLanguage} to ${targetLanguage}`);
        statusDisplay.textContent = "Translating with Azure...";

        try {
            const endpoint = `https://api.cognitive.microsofttranslator.com/translate?api-version=3.0&from=${sourceLanguage}&to=${targetLanguage}`;

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Ocp-Apim-Subscription-Key': azureKey,
                    'Ocp-Apim-Subscription-Region': azureRegion,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify([{ 'text': transcript }])
            });

            if (!response.ok) {
                throw new Error(`Azure API error: ${response.status} ${response.statusText}`);
            }

            const data = await response.json();

            if (data && data.length > 0 && data[0].translations && data[0].translations.length > 0) {
                const translatedText = data[0].translations[0].text;
                translatedTextArea.value = translatedText;
                statusDisplay.textContent = "Azure translation complete!";
            } else {
                throw new Error("Invalid response format from Azure");
            }
        } catch (error) {
            console.error("Azure translation error:", error);
            translatedTextArea.value = `Error during translation: ${error.message}. Please check your API key and try again.`;
            statusDisplay.textContent = "Translation failed.";
        }
    };

    document.getElementById('transcript').addEventListener('input', () => {
        if (document.getElementById('transcript').value.trim() !== '') {
            clearTimeout(window.translateTimeout);
            window.translateTimeout = setTimeout(translateText, 1000);
        }
    });

    const playTranslationBtn = document.getElementById('play-translation-btn');
    const translatedText = document.getElementById('translatedText');

    playTranslationBtn.addEventListener('click', () => {
        const text = translatedText.value;
        if (!text.trim()) {
            alert("No translation to speak!");
            return;
        }

        const targetLanguageCode = targetLanguageSelect.value;
        const speech = new SpeechSynthesisUtterance(text);
        speech.lang = targetLanguageCode;
        window.speechSynthesis.speak(speech);
        statusDisplay.textContent = "Playing translation...";
    });

    window.addEventListener('beforeunload', () => {
        if (recognitionStarted) {
            recognition.stop();
        }
    });
</script>

</body>
</html>