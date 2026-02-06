# Sewage System Damage Detection Rules

## Overview
This document defines the rules and indicators for detecting damaged sewage systems and wastewater issues from images.

## Visual Indicators

### 1. Water Discoloration
- **Brown or dark brown water** - Common sign of sewage contamination
- **Yellow or yellowish-green water** - Indicates wastewater discharge
- **Green or murky green water** - Contamination indicator
- **Black or dark black water** - Severe contamination
- **Gray or grayish water** - Wastewater presence
- **Unusual color** - Any unnatural water color

### 2. Foam and Bubbles
- White or colored foam on water surface
- Excessive bubbles or froth
- Foamy discharge from pipes
- Bubbling water surface
- Froth accumulation

### 3. Pipe Discharge
- Pipes discharging discolored liquid
- Outlets releasing murky water
- Drain pipes with visible waste
- Sewage pipes leaking or broken
- Manholes overflowing with liquid
- Pipe joints leaking
- Cracked or damaged pipes visible

### 4. Overflow and Backup
- Sewage overflow from manholes
- Water backing up from drains
- Sewage flooding an area
- Manhole covers displaced by pressure
- Sewage backup in basements
- Toilets or drains backing up

### 5. Contamination Signs
- Visible waste or debris in water
- Organic matter floating
- Fecal matter visible
- Trash or debris mixed with water
- Sewage solids visible
- Contaminated soil around discharge

### 6. Structural Damage
- Broken or cracked sewage pipes
- Collapsed pipe sections
- Exposed pipes with visible damage
- Damaged manhole structures
- Cracked concrete around pipes
- Erosion around pipe outlets

## Environmental Context

### Location Indicators
- Near sewage treatment facilities
- Around manholes or access points
- Near residential sewer lines
- Industrial discharge points
- Municipal sewer systems
- Wastewater treatment areas

### Surrounding Signs
- Dead vegetation near discharge
- Unusual odors (if detectable in context)
- Contaminated ground or soil
- Staining on surfaces
- Algae growth in contaminated water
- Unusual water pooling patterns

## Severity Levels

### Critical
- Raw sewage flooding public areas
- Major pipe break with large discharge
- Sewage entering water bodies
- Complete system failure
- Health hazard level contamination

### High
- Significant overflow from manholes
- Large volume discharge
- Visible waste in discharge
- Multiple pipe failures
- Contamination spreading

### Medium
- Moderate discharge visible
- Some discoloration and foam
- Single pipe issue
- Localized contamination
- Minor overflow

### Low
- Minor discoloration
- Small amount of foam
- Potential early warning signs
- Slight contamination indicators
- Preventive maintenance needed

## NOT Sewage Indicators (False Positives)

### Clear Rejections
- People, faces, portraits, selfies
- Clothing, fashion items, products
- Cartoons, anime, artwork, illustrations
- Clean, clear water
- Normal rain or rainwater
- Regular puddles without contamination
- Fountains or decorative water features
- Swimming pools
- Natural water bodies (rivers, lakes) without contamination
- Landscapes without sewage indicators
- Buildings or architecture without sewage

### Requires Sewage Indicators
- If image shows water → must have discoloration/foam to be sewage
- If image shows pipes → must show discharge/discoloration to be sewage
- If image shows outdoor scene → must show contamination to be sewage
- If image shows people → sewage must be clearly visible in background

## Detection Rules

### Must Have (at least one)
- At least one visual indicator from sewage_indicators
- Clear evidence of contamination or discharge
- Discoloration OR foam OR pipe discharge OR overflow

### Confidence Factors

#### High Confidence (0.85-1.0)
- Multiple indicators present
- Clear discoloration + foam
- Visible pipe discharge with waste
- Obvious overflow or backup

#### Medium Confidence (0.60-0.84)
- Single clear indicator
- Some discoloration visible
- Possible discharge but unclear
- Moderate contamination signs

#### Low Confidence (0.30-0.59)
- Uncertain indicators
- Possible but not clear
- Early warning signs only
- Needs closer inspection

## Analysis Process

1. **Identify** what the image actually shows - be specific about visual elements
2. **Check** for sewage indicators: discoloration, foam, pipe discharge, overflow, contamination
3. **Check** for clear false positives: people, clothing, art, clean water
4. **Determine** if sewage indicators are present AND clear
5. **Assign** confidence based on how clear the indicators are
6. **Provide** specific reasons citing the indicators found

## Usage in AI Prompt

When analyzing images, the AI should:
- Reference these indicators when making classifications
- Cite specific indicators found in the image
- Use severity levels to determine risk
- Apply confidence factors based on indicator clarity
- Reject clear false positives immediately
- Require multiple indicators for high confidence
