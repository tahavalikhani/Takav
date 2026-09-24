// Resizes and compresses owner-supplied photos without altering their content.
const sharp = require("sharp");
const fs = require("node:fs/promises");
const path = require("node:path");
const root = path.resolve(__dirname, "../theme/takav/assets/images");
(async () => {
  for (const filename of await fs.readdir(root)) {
    if (!filename.endsWith(".jpg")) continue;
    for (const width of [640, 1280]) {
      await sharp(path.join(root, filename))
        .resize({ width, withoutEnlargement: true })
        .webp({ quality: 84 })
        .toFile(path.join(root, filename.replace(".jpg", `-${width}.webp`)));
    }
  }
  console.log("Responsive WebP assets ready. Original JPEGs preserved.");
})().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
