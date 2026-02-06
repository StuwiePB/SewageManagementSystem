# Sewage Detection Rules

This folder contains the rules and indicators used by the AI system to detect damaged sewage systems and wastewater issues.

## Files

### `sewage-indicators.json`
Structured JSON file containing all sewage detection indicators, organized by category:
- Visual indicators (discoloration, foam, pipes, overflow, contamination, structural damage)
- Environmental context
- Severity levels
- False positive indicators
- Detection rules and confidence factors

### `sewage-detection-rules.md`
Human-readable markdown documentation of all rules and indicators.

## Usage

The `VisionAIService` automatically loads these rules and incorporates them into the AI prompt. The rules are loaded from `rules/sewage-indicators.json` when analyzing images.

## Updating Rules

To add new indicators or modify existing ones:

1. Edit `sewage-indicators.json` with your changes
2. The system will automatically use the updated rules on the next analysis
3. No code changes needed - just update the JSON file

## Structure

```json
{
  "sewage_indicators": {
    "visual_indicators": { ... },
    "environmental_context": { ... },
    "severity_levels": { ... },
    "not_sewage_indicators": { ... },
    "detection_rules": { ... },
    "analysis_prompt_guidance": { ... }
  }
}
```

## Adding New Indicators

To add a new indicator:

1. Open `sewage-indicators.json`
2. Find the appropriate category (e.g., `visual_indicators`)
3. Add your new indicator to the relevant array
4. Save the file
5. The AI will use it in the next analysis

## Examples

### Adding a new visual indicator:
```json
"visual_indicators": {
  "water_discoloration": [
    "Brown or dark brown water",
    "Your new indicator here"
  ]
}
```

### Adding a new severity level:
```json
"severity_levels": {
  "critical": [
    "Raw sewage flooding public areas",
    "Your new critical indicator"
  ]
}
```
