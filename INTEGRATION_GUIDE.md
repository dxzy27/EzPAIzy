# System Integration Documentation

## 1. AI Quiz Generation (Gemini API)
**Goal:** Automate the creation of quiz questions based on a topic or uploaded file.

### How it works:
1.  **Setup:** We use Google's **Gemini 1.5 Flash** model. The API key is stored securely in the `.env` file (`GEMINI_API_KEY`).
2.  **Request Construction:**
    *   When you click "Generate," the system builds a **Structured Prompt**.
    *   This prompt instructs the AI to return data in **JSON format** only (strictly enforcing a specific schema with fields like `question_text`, `options`, `correct_answer`).
    *   If a file (PDF/Text) is uploaded, we encode it (Base64) and attach it to the request so the AI can "read" the document as context.
3.  **Communication:**
    *   We use Laravel's `Http` client to send a `POST` request to `https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent`.
4.  **Processing:**
    *   The AI returns a text response. We clean up any Markdown formatting (like ```json tags) and decode the JSON into a PHP array.
    *   This array is then passed to the view to pre-fill the "Create Quiz" form.

**Why this approach?**
*   **Gemini 1.5 Flash** matches the requirement for a fast, multimodal (can read files) model that is cost-effective (free tier used).
*   **JSON Enforcement** ensures the generated questions strictly fit our database structure without manual editing.

---

## 2. Daily Quran Wisdom (Al-Quran Cloud API)
**Goal:** Provide a daily serving of spiritual guidance that is consistent for all users.

### How it works:
1.  **The API:** We use the **Al-Quran Cloud API** (`api.alquran.cloud`), which is a free, open-source REST API.
2.  **The "Daily" Logic:**
    *   The API does not have a native "Daily Verse" endpoint.
    *   **Our Algorithm:** We calculate a specific Verse ID based on the date:
        ```php
        $ayahId = (now()->dayOfYear + now()->year) % 6236 + 1;
        ```
    *   This formula ensures that on any specific date (e.g., Jan 22, 2026), the Result is always the same number (e.g., Verse #2500) for every student, but it changes the next day.
3.  **Data Fetching:**
    *   We request specific "Editions" in a single call:
        *   `quran-uthmani` (Arabic Text)
        *   `en.sahih` (English Translation)
        *   `ar.alafasy` (Audio Recitation)
4.  **Optimization (caching):**
    *   We wrap the API call in `Cache::remember`.
    *   Once the verse is fetched for the first student of the day, it is saved in the server's memory for 24 hours.
    *   Subsequent visits load instantly without hitting the external API, ensuring speed and reliability.

**Why this approach?**
*   **Consistency:** Mathematical seeding guarantees everyone discusses the same verse.
*   **Performance:** Caching prevents slow page loads.
