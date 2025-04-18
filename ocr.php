<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OCR Ingredient Analyzer</title>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5.0.0/dist/tesseract.min.js"></script>
    <style>
        body {
            background: #0d1117;
            color: #e6edf3;
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        input[type="file"] {
            margin-top: 10px;
        }

        button {
            background: #1f6feb;
            color: white;
            padding: 10px 16px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        th, td {
            border: 1px solid #30363d;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #161b22;
        }

        .faq {
            font-size: 0.95em;
            line-height: 1.5em;
        }

        #loading {
            margin-top: 10px;
            color: #ffa657;
        }
    </style>
</head>
<body>

<h2>🧠 AI-Powered Ingredient Analysis</h2>
<p>Upload an image with a list of food ingredients (comma-separated). We'll extract them and generate 5 Q&As per item.</p>

<input type="file" id="image-input" accept="image/*"><br>
<button onclick="performOCR()">Scan and Analyze</button>

<div id="loading"></div>

<table id="result-table" style="display: none;">
    <thead>
        <tr>
            <th>Ingredient</th>
            <th>FAQ (Health Analysis)</th>
        </tr>
    </thead>
    <tbody id="results-body"></tbody>
</table>

<script>
    async function performOCR() {
        const input = document.getElementById("image-input");
        const loading = document.getElementById("loading");
        const table = document.getElementById("result-table");
        const tbody = document.getElementById("results-body");

        if (!input.files[0]) {
            alert("Please upload an image.");
            return;
        }

        loading.textContent = "🕵️‍♂️ Reading text using OCR...";
        tbody.innerHTML = "";
        table.style.display = "none";

        const img = URL.createObjectURL(input.files[0]);
        const { data: { text } } = await Tesseract.recognize(img, 'eng');
        loading.textContent = "🔍 Extracted text. Generating health analysis...";

        const ingredients = text.split(",").map(i => i.trim()).filter(i => i.length > 0);

        for (const ing of ingredients) {
            const row = document.createElement("tr");
            const ingCell = document.createElement("td");
            const faqCell = document.createElement("td");

            ingCell.textContent = ing;
            faqCell.innerHTML = "<i>Loading...</i>";
            row.appendChild(ingCell);
            row.appendChild(faqCell);
            tbody.appendChild(row);

            const faqs = await getHealthFAQ(ing);
            faqCell.innerHTML = `
    <table style="width: 100%; border: 1px solid #444; border-collapse: collapse; font-size: 0.95em;">
        <thead>
            <tr>
                <th style="border: 1px solid #444; padding: 5px;">Question</th>
                <th style="border: 1px solid #444; padding: 5px;">Answer</th>
            </tr>
        </thead>
        <tbody>
            ${faqs.map(f => `
                <tr>
                    <td style="border: 1px solid #444; padding: 5px;"><b>${f.q}</b></td>
                    <td style="border: 1px solid #444; padding: 5px;">${f.a}</td>
                </tr>
            `).join("")}
        </tbody>
    </table>
`;

        }

        loading.textContent = "";
        table.style.display = "table";
    }

    async function getHealthFAQ(ingredient) {
        const prompt = `For the food ingredient "${ingredient}", provide 5 FAQs with detailed answers. Follow this format:
1. What is it?
2. Is it good for health?
3. What are the health benefits or risks?
4. Who should avoid it?
5. Are there better or healthier alternatives?`;

        try {
            const response = await fetch("https://openrouter.ai/api/v1/chat/completions", {
                method: "POST",
                headers: {
                    "Authorization": "Bearer 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",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    model: "openai/gpt-3.5-turbo",
                    messages: [{ role: "user", content: prompt }]
                })
            });

            const result = await response.json();
            const content = result.choices[0].message.content;

            const faqs = content.split(/\n(?=\d+\.)/g)
                .map(line => {
                    const parts = line.split(/(?<=\?)\s+/);
                    return {
                        q: parts[0]?.replace(/^\d+\.\s*/, "") || "Question",
                        a: parts[1] || "Answer not found."
                    };
                });

            return faqs;
        } catch (err) {
            console.error(err);
            return [{ q: "Error", a: "Could not fetch response. Check API key or server." }];
        }
    }
</script>

</body>
</html>
