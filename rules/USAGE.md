# How to Use the Sewage Detection Rules

## Overview

The rules folder contains structured indicators that guide the AI in detecting damaged sewage systems. The system automatically loads these rules and incorporates them into the AI analysis prompt.

## File Structure

```
rules/
├── sewage-indicators.json    # Structured JSON with all indicators
├── sewage-detection-rules.md # Human-readable documentation
├── README.md                  # Overview and usage guide
└── USAGE.md                   # This file
```

## How It Works

1. **Automatic Loading**: The `VisionAIService` automatically loads `rules/sewage-indicators.json` when analyzing images
2. **Prompt Building**: The `buildPromptFromRules()` method constructs the AI prompt using the indicators
3. **Dynamic Updates**: Changes to the JSON file are automatically used in the next analysis

## Adding New Indicators

### Example: Adding a New Water Discoloration Indicator

Edit `rules/sewage-indicators.json`:

```json
{
  "sewage_indicators": {
    "visual_indicators": {
      "water_discoloration": [
        "Brown or dark brown water",
        "Yellow or yellowish-green water",
        "Your new indicator here"
      ]
    }
  }
}
```

### Example: Adding a New Severity Level

```json
{
  "sewage_indicators": {
    "severity_levels": {
      "critical": [
        "Raw sewage flooding public areas",
        "Your new critical indicator"
      ]
    }
  }
}
```

## Modifying Confidence Factors

Edit the `detection_rules.confidence_factors` section:

```json
{
  "detection_rules": {
    "confidence_factors": {
      "high_confidence": [
        "Multiple indicators present",
        "Your new high confidence factor"
      ]
    }
  }
}
```

## Testing Changes

1. Edit `rules/sewage-indicators.json`
2. Upload a new incident image
3. Check the AI analysis results
4. Review logs if needed: `storage/logs/laravel.log`

## Best Practices

1. **Be Specific**: Use clear, descriptive indicators
2. **Organize**: Keep related indicators together
3. **Test**: Always test changes with real images
4. **Document**: Update `sewage-detection-rules.md` when adding major changes
5. **Version Control**: Commit changes to track what works

## Troubleshooting

### Rules Not Loading
- Check file path: `rules/sewage-indicators.json`
- Verify JSON syntax is valid
- Check logs for errors

### AI Not Following Rules
- Ensure indicators are clear and specific
- Check that confidence factors are reasonable
- Review AI response in logs

### Need to Reset
- Restore from git if needed
- Check `.gitignore` to ensure rules are tracked
