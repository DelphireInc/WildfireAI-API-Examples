## WildfireAI API Examples

### Table of contents
- [Repository structure](#repository-structure)
- [Jupyter notebook: API quickstart (for API users)](#jupyter-notebook-api-quickstart-for-api-users)
  - [Overview](#overview)
  - [Prerequisites](#prerequisites)
  - [Quick start](#quick-start)
  - [What the notebook covers](#what-the-notebook-covers)
  - [Cursor pagination (quick reference)](#cursor-pagination-quick-reference)
  - [Environment variables](#environment-variables)
  - [Helpful links](#helpful-links)
  - [Troubleshooting](#troubleshooting)
  - [Security note](#security-note)
- [WordPress: Image proxy](#wordpress-image-proxy)
  - [What you can do](#what-you-can-do)
  - [Install](#install)
  - [Use](#use)
  - [Tips](#tips)

## Repository structure
- `wildfireai_api_quickstart.ipynb`: End-to-end example showing authentication, device listing, latest records by alias, historical queries with pagination, image retrieval, and plotting.
- `wildfireai-image-proxy.php`: Minimal WordPress plugin exposing a public endpoint to proxy the latest device camera image without exposing your API key client-side.

## Jupyter notebook: API quickstart (for API users)

### Overview
This repository contains a Python Jupyter notebook demonstrating how to use the WildfireAI REST API. It shows how to authenticate with an API key, list devices, retrieve the latest records grouped by sensor aliases, query historical records with cursor pagination, and fetch images by record ID.

### Prerequisites
- **Python**: 3.9 or newer
- **Jupyter**: JupyterLab or Jupyter Notebook
- **Packages**: `requests`, `matplotlib` (the notebook will attempt to install them automatically if missing)
- A WildfireAI API key from the Portal

### Quick start
1) Clone and enter the repo:

```bash
git clone https://github.com/DelphireInc/WildfireAI-API-Examples.git
cd WildfireAI-API-Examples
```

2) (Recommended) Create and activate a virtual environment:

```bash
python -m venv .venv
source .venv/bin/activate
python -m pip install --upgrade pip
```

3) Install Jupyter and optional dependencies locally (the notebook will also install `requests` and `matplotlib` if they are missing):

```bash
pip install jupyter requests matplotlib
```

4) Set your API key (or you will be securely prompted in the notebook):

```bash
export WILDFIREAI_API_KEY="<your_api_key_here>"
```

5) Launch Jupyter and open the notebook:

```bash
jupyter lab
# or
jupyter notebook
```

Open `wildfireai_api_quickstart.ipynb` and run the cells top-to-bottom.

### What the notebook covers
- **Authentication**: Uses a Bearer token in the `Authorization` header
- **List devices**: `GET /devices` with paging
- **Latest records by alias**: `GET /devices/{deviceId}/latest`
- **Historical records**: `GET /devices/{deviceId}/sensors/{sensor}/{recordType}` with time filters and cursor pagination
- **Fetch images**: `GET /images/{imageId}`
- **Plotting**: Example plot of wind speed and angle over time using `matplotlib`

### Cursor pagination (quick reference)
- Results are returned newest-to-oldest.
- To walk toward older records, pass `olderThan` with the last record ID from the previous page.
- To walk toward newer records, pass `newerThan` with the first record ID from the previous page.

Common query parameters:
- **limit**: page size
- **minTimeRecorded** / **maxTimeRecorded**: time window filters (ISO 8601)

### Environment variables
- **WILDFIREAI_API_KEY**: Your API key. The notebook reads this variable; if absent, it will securely prompt for input.

### Helpful links
- Interactive API explorer: `https://wildfireai.com/api`
- API keys and account management (Portal): `https://wildfireai.com/portal`
- OpenAPI specification (YAML): `https://wildfireai.com/api-yaml`

### Troubleshooting
- **401 Unauthorized**: Verify the API key and that `WILDFIREAI_API_KEY` is set in the current shell/session.
- **Empty or missing image results**: Try a different device or alias (e.g., `camera_a` vs `camera_b`). Not all devices have recent image records.
- **SSL/network errors**: Check corporate proxies or firewalls. You can test connectivity with `curl https://wildfireai.com/devices` (with proper headers) or by running the notebook cell-by-cell.
- **Matplotlib errors**: Ensure `matplotlib` is installed; re-run `pip install matplotlib` in the active environment.

### Security note
Treat your API key like a password. Do not commit it to source control or share it publicly.

## WordPress: Image proxy
Add a live image from your WildfireAI device to your website using a simple WordPress plugin. No coding required.

### What you can do
- **Show the latest image** from your device’s camera A or B on any page.
- **Use a normal image tag** that you can paste into page builders or posts.

### Install
1) Download and open `wildfireai-image-proxy.php`. At the top of the file, paste your API key:

   ```php
   define( 'WFAI_API_KEY',  '<api_key>' );
   ```

   You can find your API key and device IDs in the [Portal](https://wildfireai.com/portal). Create an API key that covers the devices you want to display.

2) Upload `wildfireai-image-proxy.php` to your site at `wp-content/plugins/`.
   - If the filemanager blocks `.php` uploads, rename the file to a permitted extension and upload it.
3) In your WordPress admin dashboard, go to Plugins and activate “WildfireAI Image Proxy”.
4) Verify the plugin is working by visiting `https://your-site.example/wildfireai-image?device_id=<device_id>&cam=a` (or `&cam=b`).

### Use
- Add an image anywhere you can paste HTML. For example:

  ```html
  <img src="https://your-site.example/wildfireai-image?device_id=<device_id>&cam=a" alt="Latest camera A" />
  ```

- Use `?cam=a` for camera A, or `?cam=b` for camera B.

### Tips
- Make sure your site's server can connect to the internet and uses HTTPS.
- If no picture appears, try `?cam=b` (instead of `?cam=a`) or a different device ID, and confirm your device has recent images in the [Portal](https://wildfireai.com/portal).
