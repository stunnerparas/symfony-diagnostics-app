Hi, Thanks for allowing me to do this project!

This is my last step to get it done! Honestly, it felt like the final boss battle of a game. The requirements looked easy at first, but I did not want to just do the simple thing. I wanted to push it and make it really stand out. Even at the very end, I wanted to feel like I gave everything I had. It was an amazing experience so far.

# My App's Health Project!

This tool checks my server and application health, providing organized and configurable diagnostic information.

## ✨ Key Features:

* **Modular Design:** Easy to extend with new checks.
* **Configurable Output:** Choose specific diagnostics or predefined levels.
* **Secure Access:** Protects information using a bearer token.
* **Fast Performance:** Includes a caching layer for speed.
* **Robust Error Handling:** Provides clear messages if something goes wrong.
* **Flexible Access:** Check health using command line or a web dashboard.

## 🧠 Why I Built It This Way (My Architectural Choices)

I thought a lot about the best way to build this, aiming for a professional and maintainable system.

* **Modular Design (Tiny Experts):** Instead of one giant, confused checker, I created many small "expert" tools. Each one knows *just* its specific job (like a PHP expert or a Symfony expert). This means adding new checks is easy and does not affect existing ones. It's designed to be easily expanded.
* **The Big Boss (Collector):** I made a main "boss" that tells all these small experts what to check and collects their reports. Symfony, the main framework, automatically helps the boss find all the available experts. This acts like a very efficient manager!
* **Fancy Forms (DTOs):** All requests and reports use special "forms" called DTOs (Data Transfer Objects). This ensures consistent data structure for both input and output, reducing errors and making the API clearer for users.
* **Orderly Rooms:** I separated different types of code into specific "rooms" or folders. For example, web-related code is in one folder, security code in another. This keeps the project organized and makes it easier to find and manage code.

## ✅ Did It Break? (Testing Time!)

I made sure this thing is tough! I wrote lots of tests.

* **Unit Tests:** Each individual "expert" tool is tested on its own to confirm it works correctly.
* **Integration Tests:** I simulated real usage of the app to confirm all parts work together, including security.

## 🌟 Extra Cool Features I Squeezed In!

* **Modular Design:** Easily extend or modify diagnostics with a plug-and-play architecture.
* **Configurable Output:** Customize what checks to run and how results are displayed.
* **Secure Access:** Restrict access to diagnostics via authentication or environment-based controls.
* **Fast Performance:** Designed for speed, with minimal impact on application runtime.
* **Robust Error Handling:** Gracefully handles failures and displays helpful diagnostics messages.
* **Flexible Access (CLI & Web):** Use either the command line or a browser for full control and visibility.
* **Detailed PHP Info:** Provides an in-depth look at PHP configuration and extensions.
* **Symfony Application Details:** Displays Symfony-specific insights like environment, debug mode, and more.
* **System Resource Monitoring:** Tracks CPU, memory, disk, and other vital system metrics.
* **Application Version Check:** Clearly shows the currently deployed app version for verification.
* **Command Line Tool:** A dedicated `app:diagnostics` command for quick checks in your terminal.
* **Web Dashboard:** A simple web page at `/diagnostics` for a visual overview.
* **Caching Layer:** Implemented caching so repeated checks are super fast.
* **Monitoring Integration Ready:** Includes a placeholder for integrating with advanced monitoring systems like Prometheus.

## My AI Tools Usage

I used AI tools to assist with development:

* **GitHub Copilot:** Helped generate boilerplate code, method stubs, and provided intelligent code completion, significantly speeding up development.
* **ChatGPT/Claude:** Provided guidance on architectural patterns, advanced Symfony features, and best practices for structuring a modular application, which influenced key design decisions.
* **Gemini:** Used for in-depth research on specific Symfony components and exploring alternative implementations for complex features. It helped confirm best practices and broaden my understanding of framework capabilities.



-------------------------------------
## 🏃‍♀️ Getting Started:

Follow these steps to set up and run the application:

1.  **Clone the Repository:**
    ```bash
    git clone
    ```
2.  **Navigate to the Project Directory:**
    ```bash
    cd diagnostics
    ```
3.  **Install Dependencies:**
    ```bash
    composer install
    ```
4.  **Configure API Token & Secret Key:**
    Create or open your `.env.local` file (and `.env.test.local` for testing) and ensure the following environment variables are set:

    ```env
    # .env.local
    APP_SECRET="[A_LONG_RANDOM_STRING_GENERATED_BY_SYMFONY]" # Run 'php bin/console secrets:generate-keys' or set manually
    APP_API_TOKEN="paras_for_pimcore_2025"
    ```

5.  **Clear Cache & Start Server:**
    Ensure no processes are locking files, then clear the cache and start the Symfony local server:
    ```bash
    # Stop XAMPP Apache/MySQL if running, and stop any existing Symfony server (Ctrl+C multiple times)
    rmdir /s /q var\cache
    php bin/console cache:clear --env=dev
    composer dump-autoload
    symfony local:server:start
    ```

6.  **Authenticate (Get your Token):**
    Use the following command to log in and receive your API token.
    * **Login Password:** `paras_for_pimcore_2025`
    * **API Token (issued):** `paras_for_pimcore_2025`

    ```bash
    curl -X POST "[http://127.0.0.1:8000/api/login_check](http://127.0.0.1:8000/api/login_check)" \
    -H "Content-Type: application/json" \
    -d "{\"username\": \"api_user\", \"password\": \"paras_for_pimcore_2025\"}"
    ```
    Copy the `token` value from the response (`paras_for_pimcore_2025`).

7.  **Access Diagnostics (Using the Token):**
    To fetch **PHP, Symfony, and Application diagnostics**, use the copied token in the `Authorization` header and include all three categories in the `include` array:

    ```bash
    curl -X POST "[http://127.0.0.1:8000/api/system/diagnostics](http://127.0.0.1:8000/api/system/diagnostics)" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer paras_special_token_2025" \
    -d "{\"include\": [\"php\", \"symfony\", \"application\"]}"
    ```

    <b>You can also visit `/diagnostics` in your browser for the web dashboard.</b>
<br><br>
7.  Thank you allowing me to work on this demo project. I hope we can solve the bigger Tech problem of this world together.
<br><br>
