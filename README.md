## ImageCompressor (Laravel + Jetstream)

A simple image compression service with a guest-facing UI and token-protected API.

### Preview
![App Screenshot](screenhot.png)

### Features
- Guest UI at `/` with drag-and-drop upload
- Lossless PNG optimization; JPEG/JPG/WebP recompress with adjustable quality
- Never returns a file larger than original
- API tokens via Jetstream (Sanctum) for programmatic usage

### Requirements
- PHP 8.2+
- GD extension (for PNG/JPEG/WebP functions)
- Node 18+ (for building assets)
- Optional: Imagick (not required)

### Setup
```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --graceful
npm install
```

### Run (dev)
```bash
npm run dev
php artisan serve
```
Open `http://127.0.0.1:8000/`.

### Build (prod)
```bash
npm run build
php artisan serve
```

### Web UI
- Visit `/` to upload an image and download the compressed result.
- Slider controls JPEG/WebP quality (default 75 in UI).

### API
- Endpoint: `POST /api/v1/compress`
- Auth: Bearer token (create under Profile → API Tokens)
- Form fields:
  - `image`: file (jpeg/jpg/png/webp) up to 10 MB
  - `quality` (optional): 1–100 (affects JPEG/WebP; PNG stays lossless)

#### Download response
Set header `Accept: application/octet-stream` to receive a binary file response.
```bash
curl -X POST http://localhost:8000/api/v1/compress \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/octet-stream" \
  -F "image=@/path/to/image.jpg" \
  -F "quality=75" \
  --output image-compressed.jpg
```

#### JSON response
Without the Accept header above, you get JSON:
```json
{
  "filename": "example-compressed.jpg",
  "mime": "image/jpeg",
  "original_bytes": 123456,
  "compressed_bytes": 84567,
  "data_base64": "..."
}
```

### Using from another Laravel app
Add to the client app `.env`:
```env
IMGCOMPRESSOR_BASE_URL=http://localhost:8000
IMGCOMPRESSOR_TOKEN=your_token
```

Create a small client (example):
```php
use Illuminate\Support\Facades\Http;

$response = Http::withToken(env('IMGCOMPRESSOR_TOKEN'))
    ->accept('application/octet-stream')
    ->attach('image', fopen($path, 'r'), basename($path))
    ->post(rtrim(env('IMGCOMPRESSOR_BASE_URL'), '/').'/api/v1/compress', [
        'quality' => 75,
    ]);

file_put_contents($target, $response->body());
```

### Notes
- If assets hang loading on first run, ensure `npm run dev` (Vite) is running or build with `npm run build`.
- If cache clear hangs and DB isn’t running, set `CACHE_STORE=file` in `.env`.

### License
MIT
