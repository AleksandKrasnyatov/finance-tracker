# Finance tracker

### Launch instruction
1. Init project
    ```bash
    make init
    ```
2. Add environment variables to [.env](.env) file
3. Start listening for Telegram updates, 2 ways:
    1. Polling
        ```bash
        # Start polling command
        make telegram-polling
        ```
    2. Webhook
        ```bash
        # Add Webhook command
        make telegram-add-webhook
        ```
        ```bash
        # Delete Webhook command
        make telegram-delete-webhook
        ```
