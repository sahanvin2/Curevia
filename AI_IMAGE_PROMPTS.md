# AI Image Generation Prompts for Curevia

Use these prompts to generate article and story images that match Curevia style.

## 1) Featured Image Prompt Template

```
Create a high-detail editorial illustration for a knowledge encyclopedia article.
Topic: {{TOPIC_TITLE}}
Category: {{CATEGORY_NAME}}
Visual style: cinematic, realistic lighting, clean composition, high contrast, no text, no watermark, no logo.
Mood: {{MOOD}}
Color direction: deep navy background accents with natural highlight colors.
Framing: landscape 16:9, strong focal subject centered, background depth, publication-ready.
Quality: ultra sharp, professional, 4k.
Negative prompt: blurry, low detail, text overlays, logos, watermark, extra limbs, distortion, oversaturated neon.
```

## 2) Gallery Image Prompt Template

```
Generate a supplementary encyclopedia gallery image.
Topic: {{TOPIC_TITLE}}
Subtopic: {{SUBTOPIC}}
Style: documentary realism, consistent with featured image palette, no text, no watermark.
Framing: landscape 4:3, clean subject isolation, natural depth of field.
Quality: high detail, publication-grade, balanced contrast.
Negative prompt: text, logo, watermark, heavy artifacts, blur, cartoon style, duplicated subjects.
```

## 3) Section-Specific Prompt Template

```
Generate a section illustration for an educational article.
Article: {{TOPIC_TITLE}}
Section heading: {{SECTION_TITLE}}
Section summary: {{SECTION_SUMMARY}}
Visual requirement: accurate, educational, realistic, no labels or text in image.
Composition: medium-wide shot, clear primary subject, uncluttered background.
Output: 16:9, high-resolution, professional editorial style.
```

## 4) Good Prompt Examples

### Ancient Egypt Civilization

```
Create a cinematic editorial illustration of Ancient Egypt civilization, showing pyramids at golden hour, Nile river in the foreground, workers and stone architecture details, realistic desert atmosphere, no text, no logo, no watermark, landscape 16:9, high detail, publication-ready.
```

### Wildlife Protection Laws

```
Create a realistic editorial image for Wildlife Protection Laws featuring a protected forest corridor with ranger observation point, biodiversity focus, soft morning light, documentary style, no text, no logo, no watermark, landscape 16:9, sharp details, clean composition.
```

## 5) Image Management Workflow

1. Generate image using one of the templates.
2. Upload in editor via Featured Image dropzone or Gallery dropzone.
3. Save article/story.
4. To replace featured image, upload a new one and save.
5. To delete featured image, check Remove current featured image and save.
6. To delete gallery images, click Remove on each gallery item and save.

## 6) Consistency Rules

- Keep one visual style per article.
- Do not mix photoreal and cartoon assets in one article.
- Avoid text in generated images.
- Keep topic accuracy first; style second.
- Prefer natural colors over extreme saturation.
