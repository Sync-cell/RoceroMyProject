<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Site Unreachable</title>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    height: 100vh;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    font-family: Arial, sans-serif;
                    background: #f5f5f5;
                    color: #333;
                    text-align: center;
                }
                .container {
                    max-width: 500px;
                }
                .icon {
                    font-size: 80px;
                    margin-bottom: 20px;
                }
                h1 {
                    font-size: 24px;
                    margin-bottom: 10px;
                }
                p {
                    font-size: 16px;
                    margin: 5px 0;
                    color: #555;
                }
                .btn-refresh {
                    display: inline-block;
                    padding: 10px 20px;
                    font-size: 16px;
                    background: #0078d7;
                    color: #fff;
                    text-decoration: none;
                    border-radius: 4px;
                    margin-top: 15px;
                }
                .btn-refresh:hover {
                    background: #005a9e;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="icon">☁️</div>
                <h1>Hmm... can\'t reach this page</h1>
                <p>Your IP has been blocked by the system.</p>
                <p>Try refreshing or contact the administrator if you think this is an error.</p>
                <a href="#" onclick="location.reload();" class="btn-refresh">Refresh</a>
            </div>
        </body>
        </html>