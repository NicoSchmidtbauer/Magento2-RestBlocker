# Magento 2 REST File Upload Blocker (PolyShell Mitigation and possible others)

## Overview

This module provides a lightweight security mitigation for Magento 2 stores by blocking malicious REST API requests that attempt to upload files via the `file_info` parameter.

It is specifically designed as a **temporary protection layer** against exploits such as the *PolyShell vulnerability*, which abuse Magento's custom options file upload functionality via REST endpoints.

In case if future vulnerabilities, that need further hardening of the Rest API, feel free to add endpoints to the code.

---

## What It Does

The module intercepts incoming REST API requests before anything is written to the disk and:

* Targets the following endpoints:

  * `/rest/V1/guest-carts/*`
  * `/rest/V1/carts/*`
  * `/rest/default/V1/guest-carts/*`
  * `/rest/default/V1/carts/*`
* Inspects the request body for the presence of `file_info`
* Blocks the request if `file_info` is detected (including nested structures)
* Returns a clean HTTP `400 Bad Request` response

---

## Why This Exists

Certain Magento 2 versions (e.g. 2.4.3) are vulnerable to attacks that:

* Upload malicious PHP payloads via base64-encoded data
* Abuse file upload mechanisms in product custom options
* Potentially lead to **remote code execution (RCE)**

This module acts as a **defensive filter** to block such payloads at the application level.

---

## Important Notes

⚠️ **This is NOT a full security fix.**

* It does **not patch the underlying vulnerability**
* It does **not remove existing backdoors or malware**
* It should be used as a **temporary mitigation only**

👉 Recommended use cases:

* Short-term protection for legacy Magento installations
* Transition periods (e.g. migration to another platform)
* Emergency hardening when server-level access is unavailable

---

## Installation

1. Copy the module to:

```
app/code/JscDesign/RestBlocker
```

2. Enable the module:

```bash
bin/magento module:enable JscDesign_RestBlocker
bin/magento setup:upgrade
bin/magento cache:flush
```

3. (Optional, production mode only):

```bash
bin/magento setup:di:compile
```

---

## How It Works

The module uses a Magento plugin on:

```
Magento\Webapi\Controller\Rest::dispatch
```

Before the request is processed, it:

1. Checks if the request targets a cart-related REST endpoint
2. Reads the raw request body
3. Performs a fast string check for `file_info`
4. Parses JSON and recursively scans for `file_info`
5. Throws an exception to Magentos exception log if detected

---

## Testing

### Example (PowerShell with Basic Auth)

```powershell
$uri = "https://yourshop/rest/V1/guest-carts/test/items"

$headers = @{
    "Content-Type" = "application/json"
}

$body = @{
    file_info = "test"
} | ConvertTo-Json

Invoke-RestMethod -Uri $uri -Method POST -Headers $headers -Body $body
```

Expected result:

* HTTP 400 response
* Error message indicating the request was blocked

---

## Limitations

* Only protects specific REST endpoints
* Does not inspect non-REST attack vectors
* Can be bypassed if attackers use alternative exploitation paths
* Slight overhead due to request inspection (minimal impact in practice)

---

## Recommendations

For proper security, you should also:

* Upgrade to a patched Magento version
* Audit your system for compromised files:

  * `pub/media`
  * `var/`
* Disable PHP execution in upload directories
* Review admin users and cron jobs

---

## License

Free

---

## Disclaimer

This module is provided **as-is**, without warranty of any kind.

Use it at your own risk. It is intended as a temporary mitigation and should not replace proper security updates and system hardening.

---
