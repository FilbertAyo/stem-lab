# STEM Lab — Project Content Preparation Template

Use this template to prepare content **before** uploading to the system. Fill in each section for your discipline (Robotics, Aviation, Coding, or Mathematics). When everything is ready, upload via the admin panel.

---

## 1. Basic project info (all disciplines)

| Field | What to provide | Example |
|-------|------------------|---------|
| **Title** | Clear project name | "Smart Plant Watering System" |
| **Short description** | 1–2 sentences for cards/preview (max ~100–150 chars) | "Build an automatic plant waterer using a soil sensor and Arduino." |
| **Full description / guide** | Full text: what the project is, what students will learn, any intro or context | Full paragraph(s) explaining the project and learning goals. |
| **Cover image** | One main image (e.g. JPG/PNG), recommended size e.g. 1200×630 or 16:10 | `cover.jpg` |
| **Difficulty** | beginner / intermediate / advanced | beginner |
| **Estimated time** | How long (in minutes) to complete | 120 |
| **YouTube URL** (optional) | Link to a demo or tutorial video | https://youtube.com/watch?v=... |
| **External link** (optional) | Any other web resource (docs, simulator, etc.) | https://... |
| **Tags** | Keywords for search/filter (comma or list) | Arduino, sensors, plants, automation |
| **Platforms** | Tools used (e.g. Arduino, Python, Scratch, simulator name) | Arduino, Breadboard |

---

## 2. Discipline-specific extras

### Robotics
- **Circuit image** (optional): Diagram or photo of the circuit.
- **Steps**: Use the **Steps** section below. For each step include: title, description, optional image, optional code snippet (e.g. Arduino).
- **Code blocks**: Use the **Code blocks** section if you have full programs (e.g. Arduino C, MicroPython).
- **Equipment** (if your system uses it): List parts and quantities (e.g. 1× Arduino Uno, 1× soil sensor, 1× water pump).

### Aviation
- **Steps**: Use the **Steps** section for procedures (e.g. pre-flight check, flight plan, simulator steps).
- **Resources**: Use the **Resources** section for PDFs, checklists, or links (e.g. flight manual, simulator link).
- **Platforms**: e.g. Flight simulator name, type of aircraft or scenario.

### Coding
- **Steps** (optional): Use if the project is step-by-step (e.g. “Step 1: Set up project”, “Step 2: Write function”).
- **Code blocks**: Use the **Code blocks** section for each program or snippet (title, language, code, short description).
- **Resources**: Links to docs, starter code, or exercises (PDF/code/link).
- **Platforms**: e.g. Python, Scratch, Web, Thonny.

### Mathematics
- **Steps**: Use for worked examples or solution steps (title, description, optional image for diagrams).
- **Resources**: PDFs or links to problem sets, formula sheets, or tools (e.g. Desmos, GeoGebra).
- **Code blocks** (optional): If using code (e.g. Python, Desmos) for calculations or graphs.
- **Platforms**: e.g. Calculator, GeoGebra, Python, Pen and paper.

---

## 3. Steps (use when project has step-by-step instructions)

Add one block per step. Not all projects need steps.

| Step # | Title | Description | Image (optional) | Code snippet (optional) |
|--------|-------|-------------|-------------------|--------------------------|
| 1 | | | | |
| 2 | | | | |
| 3 | | | | |
| ... | | | | |

**Example (Robotics):**

| Step # | Title | Description | Image | Code snippet |
|--------|-------|-------------|-------|--------------|
| 1 | Connect soil sensor | Connect VCC to 5V, GND to GND, signal to A0. | `step1-wiring.jpg` | — |
| 2 | Upload code | Upload the moisture reading sketch. | — | (paste snippet or reference code block below) |

---

## 4. Code blocks (for coding / robotics / math with code)

One block per program or snippet. Use when you have full code or important snippets.

| Order | Title | Language | Code | Short description |
|-------|-------|----------|------|-------------------|
| 1 | | (e.g. Arduino C, Python, Scratch) | (paste code) | |
| 2 | | | | |

**Example:**

| Order | Title | Language | Code | Short description |
|-------|-------|----------|------|-------------------|
| 1 | Moisture read & pump control | Arduino C | `if(moisture < 300) pumpOn();` ... | Reads sensor and turns pump on when dry. |

---

## 5. Resources (files or links)

PDFs, code files, images, or external links. One row per resource.

| Order | Title | Type | File or URL |
|-------|-------|------|-------------|
| 1 | | (pdf / code / image / link) | path or URL |
| 2 | | | |

**Example:**

| Order | Title | Type | File or URL |
|-------|-------|------|-------------|
| 1 | Circuit diagram | image | `circuit-diagram.png` |
| 2 | Arduino reference | link | https://www.arduino.cc/reference |

---

## 6. Quick checklist before upload

- [ ] Title and short description written
- [ ] Full description / guide written
- [ ] Cover image ready
- [ ] Difficulty and estimated time set
- [ ] Category chosen: Robotics / Aviation / Coding / Mathematics
- [ ] Tags and platforms listed
- [ ] Steps filled (if needed)
- [ ] Code blocks prepared (if needed)
- [ ] Resources collected (files + links) (if needed)
- [ ] YouTube / external link added (if applicable)
- [ ] Circuit image added (Robotics, if applicable)

---

## 7. Empty copy-paste template (minimal)

Use this as a blank form to duplicate and fill.

```
TITLE:
SHORT DESCRIPTION (1–2 sentences):
FULL DESCRIPTION / GUIDE:

COVER IMAGE filename:
CIRCUIT IMAGE filename (Robotics only, optional):

DIFFICULTY: [ ] beginner  [ ] intermediate  [ ] advanced
ESTIMATED TIME (minutes):
CATEGORY: [ ] Robotics  [ ] Aviation  [ ] Coding  [ ] Mathematics

YOUTUBE URL:
EXTERNAL LINK:
TAGS (comma-separated):
PLATFORMS (comma-separated):

--- STEPS (add as many as needed) ---
Step 1 – Title:     Description:     Image:     Code snippet:
Step 2 – Title:     Description:     Image:     Code snippet:

--- CODE BLOCKS (add as many as needed) ---
Block 1 – Title:     Language:     Code:     Description:
Block 2 – Title:     Language:     Code:     Description:

--- RESOURCES ---
Resource 1 – Title:     Type:     File/URL:
Resource 2 – Title:     Type:     File/URL:
```

---

*After filling this template, upload the project in the admin panel and attach the steps, code blocks, resources, tags, and platforms as in the system guide.*
