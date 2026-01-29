## Installation

1. Copy this folder to: `wp-content/plugins/wp-odds-comparison`
2. Activate "WP Odds Comparison" in WordPress admin
3. Configure settings in the "Odds Comparison" admin menu

## How It Works

### Bookmaker Setup

The plugin includes **10 bookmakers**:

1. **Oddschecker** - This is the only real scraper. It fetches actual odds from Oddschecker.com
2. **Bookmaker A through J** - These are dummy bookmakers that generate random (but consistent) odds for demonstration purposes

This setup was chosen to show how the plugin would work with multiple bookmakers while only requiring one working scraper for the assignment.

### Scraping System

- **Example_Oddschecker_Scraper.php** - Scrapes real data from Oddschecker website
- **Dummy_Scraper.php** - Generates fake but realistic odds for the dummy bookmakers
- **Base_Scraper.php** - Provides common HTTP and HTML parsing functions
- **Scraper_Factory.php** - Creates the right type of scraper for each bookmaker

### Odds Conversion

The `Odds_Converter` class handles all format conversions:
- First normalizes all odds to decimal format
- Then converts to the requested format (decimal/fractional/American)
- Uses proper mathematical formulas for accurate conversions

### Caching

Odds data is cached for 60 seconds using WordPress transients. This prevents hammering the Oddschecker website and improves page load times.

## Using the Gutenberg Block

1. Create or edit a post/page
2. Add the "Odds Comparison" block
3. Configure:
   - **Event Slug** - The Oddschecker URL path (e.g., `football/premier-league/liverpool-v-man-city/winner`)
   - **Odds Format** - Choose decimal, fractional, or American
   - **Bookmakers** - Select which bookmakers to display
   - **Markets** - Which betting markets to show (e.g., Match Winner, Over/Under)
4. Publish and view the odds table on the front-end

## Admin Settings

Navigate to **Odds Comparison** in the WordPress admin menu to configure:

- **Enabled Bookmakers** - Which bookmakers to show by default
- **Markets** - Comma-separated list of markets (e.g., `Match Winner, Over/Under 2.5`)
- **Bookmaker Links** - URLs where users can place bets


### WordPress Integration

- Uses WordPress HTTP API (`wp_remote_get`) for web requests
- Stores settings via Options API
- Implements caching with Transients API



**Important:** Only the Oddschecker scraper actually fetches real data. The other 9 bookmakers are placeholders to demonstrate the multi-bookmaker comparison functionality without requiring 10 working scrapers.

## Future Enhancements

To make this production-ready, you would want to:
- Add more real scrapers for actual bookmaker websites
- Implement better error logging
- Add rate limiting to respect website terms of service
- Create unit tests for the odds conversion logic
- Add AJAX loading for better performance
- Implement background cron jobs for data fetching
