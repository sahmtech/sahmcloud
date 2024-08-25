# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {access_token}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

<p><strong>Getting access_token</strong></p><ol><li><strong>Get client_id &amp; client_secret: </strong>Login to your account. Go to Connector &gt; Clients, click on Create Client button.</li><li><strong>Requesting Tokens: </strong>Send request with client_id, client_secret, username &amp; password as given in <a href="https://laravel.com/docs/9.x/passport#requesting-password-grant-tokens" target="_blank">laravel documentation</a>. In response, you get access_token</li></ol>
