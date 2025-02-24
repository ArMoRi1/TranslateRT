<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>AI Translate</title>
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
    </style>
</head>
<body>

<!-- Starting prompt input -->
<div>
    <div>
        <label for="startingPrompt">Starting Prompt (e.g., "English, Ukrainian")</label>
    </div>
    <textarea name="startingPrompt" id="startingPrompt" cols="40" rows="3" placeholder="Enter source and target languages"></textarea>
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
    <p>AI translates language A to language B based on the prompt</p>
    <textarea id="translatedText" rows="5" cols="40" placeholder="Translated text will appear here..."></textarea>
</div>

<!-- Text-to-Speech Output -->
<div>
    <h2>Speech Output</h2>
    <button id="play-translation-btn">Play Translation</button>
</div>

<!-- Scripts for voice input and text-to-speech -->
<script>
    // Voice input functionality (use Web Speech API)
    const startRecordBtn = document.getElementById('start-record-btn');
    const statusDisplay = document.getElementById('status');
    const transcriptDisplay = document.getElementById('transcript');

    // Language code mapping (for common languages)
    const languageCodeMap = {
        'english': 'en',
        'ukrainian': 'uk',
        'spanish': 'es',
        'french': 'fr',
        'german': 'de',
        'italian': 'it',
        'russian': 'ru',
        'japanese': 'ja',
        'chinese': 'zh',
        'polish': 'pl',
        'turkish': 'tr',
        'arabic': 'ar',
        'dutch': 'nl',
        'portuguese': 'pt',
        'korean': 'ko',
        'swedish': 'sv',
        'hindi': 'hi',
        'greek': 'el'
    };

    // Get language code from language name
    function getLanguageCode(languageName) {
        const lowercaseName = languageName.toLowerCase();
        return languageCodeMap[lowercaseName] || lowercaseName;
    }

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();
    let recognitionStarted = false;

    startRecordBtn.addEventListener('click', () => {
        if (!recognitionStarted) {
            const prompt = document.getElementById('startingPrompt').value;
            const languages = prompt.split(',').map(lang => lang.trim());

            if (languages.length >= 1) {
                // Set the source language for speech recognition if specified
                const sourceLanguageCode = getLanguageCode(languages[0]);
                recognition.lang = sourceLanguageCode;
                console.log(`Setting recognition language to: ${sourceLanguageCode}`);
            }

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

        // Perform translation based on the entered languages
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

    // Function to perform actual translation
    const translateText = async () => {
        const prompt = document.getElementById('startingPrompt').value;
        const transcript = document.getElementById('transcript').value;
        const translatedTextArea = document.getElementById('translatedText');

        if (!transcript.trim()) {
            console.log("No text to translate");
            return;
        }

        // Extract the source and target languages from the starting prompt
        const languages = prompt.split(',').map(lang => lang.trim());

        if (languages.length !== 2) {
            alert("Please enter both source and target languages separated by a comma.");
            return;
        }

        const sourceLanguage = languages[0];
        const targetLanguage = languages[1];

        // Get ISO language codes
        const sourceCode = getLanguageCode(sourceLanguage);
        const targetCode = getLanguageCode(targetLanguage);

        console.log(`Translating from ${sourceCode} to ${targetCode}`);
        statusDisplay.textContent = "Translating...";

        try {
            // Use a public API for translation - this is a client-side approach
            // Note: For production, you should use a proper paid API with authentication
            const url = `https://translate.googleapis.com/translate_a/single?client=gtx&sl=${sourceCode}&tl=${targetCode}&dt=t&q=${encodeURIComponent(transcript)}`;

            const response = await fetch(url);
            const data = await response.json();

            // The response format is a bit complex, extract the translation
            let translatedText = '';
            if (data && data[0]) {
                for (let i = 0; i < data[0].length; i++) {
                    if (data[0][i][0]) {
                        translatedText += data[0][i][0];
                    }
                }
            }

            console.log("Translation received:", translatedText);
            translatedTextArea.value = translatedText;
            statusDisplay.textContent = "Translation complete!";
        } catch (error) {
            console.error("Translation error:", error);
            translatedTextArea.value = "Error during translation. Please try again.";
            statusDisplay.textContent = "Translation failed.";
        }
    };

    // Manual translation when transcript is typed
    document.getElementById('transcript').addEventListener('input', () => {
        if (document.getElementById('transcript').value.trim() !== '') {
            // Wait a bit before translating to avoid too many requests while typing
            clearTimeout(window.translateTimeout);
            window.translateTimeout = setTimeout(translateText, 1000);
        }
    });

    // Text-to-Speech functionality
    const playTranslationBtn = document.getElementById('play-translation-btn');
    const translatedText = document.getElementById('translatedText');

    playTranslationBtn.addEventListener('click', () => {
        const text = translatedText.value;
        if (!text.trim()) {
            alert("No translation to speak!");
            return;
        }

        const languages = document.getElementById('startingPrompt').value.split(',').map(lang => lang.trim());
        if (languages.length >= 2) {
            const targetLanguageCode = getLanguageCode(languages[1]);

            const speech = new SpeechSynthesisUtterance(text);
            speech.lang = targetLanguageCode;
            window.speechSynthesis.speak(speech);
            statusDisplay.textContent = "Playing translation...";
        } else {
            const speech = new SpeechSynthesisUtterance(text);
            window.speechSynthesis.speak(speech);
        }
    });

    // Make sure recognition is canceled when page unloads
    window.addEventListener('beforeunload', () => {
        if (recognitionStarted) {
            recognition.stop();
        }
    });
</script>

</body>
</html>