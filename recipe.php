<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>🍲 Smart Recipe Generator</title>
  <style>
    body {
      background: #0d1117;
      color: #e6edf3;
      font-family: 'Segoe UI', sans-serif;
      padding: 20px;
    }
    input, textarea, select, button {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      background: #161b22;
      border: 1px solid #30363d;
      color: #e6edf3;
      border-radius: 8px;
    }
    button {
      background-color: #238636;
      font-weight: bold;
      cursor: pointer;
    }
    button:hover {
      background-color: #2ea043;
    }
    .output-card {
      background: #1a1f2b;
      padding: 20px;
      margin-top: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(56,139,253,0.2);
    }
    h2 {
      color: #58a6ff;
    }
    label {
      font-weight: bold;
    }
  </style>
</head>
<body>

  <h2>🍲 Smart AI Recipe Generator</h2>

  <label>🧾 Ingredients You Have</label>
  <textarea id="ingredients" rows="3" placeholder="e.g., potatoes, mustard oil, poppy seeds"></textarea>

  <label>🥗 Dietary Preference</label>
  <select id="diet">
    <option value="everything">Everything</option>
    <option value="vegetarian">Vegetarian</option>
    <option value="non-vegetarian">Non-Vegetarian</option>
    <option value="vegan">Vegan</option>
  </select>

  <label>👤 For whom?</label>
  <input type="text" id="forWhom" placeholder="e.g., gym-goer, diabetic mother, hostel student"/>

  <label>🔥 Calorie Count (Optional)</label>
  <input type="number" id="calories" placeholder="e.g., 500"/>

  <label>⏱️ Time Limit (in minutes)</label>
  <input type="number" id="timeLimit" placeholder="e.g., 20"/>

  <label>🚫 Allergies or Dietary Restrictions</label>
  <input type="text" id="allergies" placeholder="e.g., nuts, gluten"/>

  <label>🏠 Are you a hostel student with limited pantry items?</label>
  <select id="hostel">
    <option value="no">No</option>
    <option value="yes">Yes</option>
  </select>

  <label>🎯 Goal</label>
  <select id="goal">
    <option value="balanced">Balanced Diet</option>
    <option value="weight_loss">Weight Loss</option>
    <option value="bulking">Bulking</option>
    <option value="fasting">Intermittent Fasting</option>
  </select>

  <label>👟 Step Count / Fatigue</label>
  <select id="fatigue">
    <option value="light">Low Energy (Light Meal)</option>
    <option value="power">High Energy (Power Meal)</option>
  </select>

  <button onclick="generateRecipe()">✨ Generate Recipe</button>

  <div id="output"></div>

  <script>
    async function generateRecipe() {
      const ingredients = document.getElementById("ingredients").value;
      const diet = document.getElementById("diet").value;
      const forWhom = document.getElementById("forWhom").value;
      const calories = document.getElementById("calories").value;
      const time = document.getElementById("timeLimit").value;
      const allergies = document.getElementById("allergies").value;
      const hostel = document.getElementById("hostel").value;
      const goal = document.getElementById("goal").value;
      const fatigue = document.getElementById("fatigue").value;

      const prompt = `
You are a smart AI Chef. Based on the following inputs, suggest a creative recipe:

Ingredients: ${ingredients}
Dietary preference: ${diet}
Who is this for: ${forWhom}
Calories limit: ${calories ? calories + " kcal" : "Not specified"}
Time available: ${time ? time + " minutes" : "Not specified"}
Allergies/Restrictions: ${allergies}
Hostel Mode: ${hostel === 'yes' ? "Yes - Limited pantry items" : "No"}
Goal: ${goal}
Meal Type: ${fatigue === 'light' ? "Light meal" : "Power meal"}

Generate:
- 🍛 Recipe name
- 🌍 Regional dish suggestion (if possible)
- ✅ Ingredients required
- ⏱️ Cooking time
- 🔥 Estimated calories
- 👨‍🍳 Step-by-step instructions
- ❤️ Why it's suitable for ${forWhom}
Keep it short, readable, and well formatted.
`;

      const output = document.getElementById("output");
      output.innerHTML = "⏳ Generating your recipe...";

      try {
        const response = await fetch("https://openrouter.ai/api/v1/chat/completions", {
          method: "POST",
          headers: {
            "Authorization": "Bearer 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",  // <-- Replace with your key
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            model: "openai/gpt-3.5-turbo",
            messages: [{ role: "user", content: prompt }]
          })
        });

        const result = await response.json();
        const content = result.choices[0].message.content;

        output.innerHTML = `
          <div class="output-card">
            <pre style="white-space:pre-wrap;">${content}</pre>
          </div>`;
      } catch (err) {
        console.error(err);
        output.innerHTML = "❌ Failed to fetch recipe. Try again later.";
      }
    }
  </script>

</body>
</html>
