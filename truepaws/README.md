# TruePaws - Kennel Management System

**TruePaws** is a comprehensive, self-hosted kennel management plugin for WordPress, designed specifically for professional dog and cat breeders. It provides a modern, app-like interface for managing animal husbandry, health records, reproduction tracking, and sales.

## Features

### 🐕 Animal Management
- Complete animal profiles with photos, lineage, and health history
- Timeline view of all events (birth, vaccinations, vet visits, etc.)
- Media gallery integration with WordPress
- Status tracking (active, retired, sold, deceased, co-owned)

### 🧬 Pedigree & Lineage
- Automatic pedigree generation (3+ generations)
- Visual pedigree tree display
- PDF pedigree certificates
- Support for "ghost" ancestors

### 👨‍👩‍👧‍👦 Reproduction Tracking
- Litter management with mating logs
- Pregnancy calculator with expected whelping dates
- Whelping wizard for batch puppy creation
- Automated puppy record generation

### 💉 Health & Protocols
- Event timeline for each animal
- Vaccine and treatment tracking
- Vet visit records
- Bulk health event logging

### 💰 Sales & Contacts
- Contact management for buyers and waitlists
- Sales integration with animal records
- Automated handover packet generation
- PDF contracts and health certificates

### 🌐 Public Integration
- Shortcodes for website integration
- Available puppies gallery
- Individual animal profiles
- Litter showcase pages

## Installation

1. Download the plugin as a ZIP file
2. Upload to your WordPress site via **Plugins > Add New > Upload Plugin**
3. Activate the plugin
4. Visit **TruePaws** in your WordPress admin menu

## Quick Start

1. **Configure Settings**: Set your breeder prefix and default species
2. **Add Parent Animals**: Create records for your breeding stock
3. **Log Matings**: Record breeding pairs and calculate due dates
4. **Track Whelping**: Use the whelping wizard to add litters
5. **Manage Sales**: Link buyers to puppies and generate handover documents

## Shortcodes

### Display Available Puppies
```
[truepaws_available_puppies limit="12"]
```

### Show Specific Litter
```
[truepaws_litter id="123"]
```

### Display Animal Profile
```
[truepaws_animal id="456" show_pedigree="true"]
```

## Database Tables

The plugin creates these custom tables:
- `wp_bm_animals` - Animal registry
- `wp_bm_events` - Timeline events
- `wp_bm_contacts` - Buyers and contacts
- `wp_bm_litters` - Reproduction tracking

## Security

- All REST API endpoints require WordPress admin permissions
- Input sanitization and validation
- Nonce verification for state-changing operations
- Prepared SQL statements prevent injection

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- WordPress 5.0+ required
- PHP 7.4+ recommended

## Development

### Build Requirements
- Node.js 16+
- npm or yarn

### Building for Production
```bash
cd truepaws
npm install
npm run build
```

### Development Mode
```bash
npm run dev
```

## API Reference

### REST Endpoints

All endpoints are available under `/wp-json/truepaws/v1/`:

- `GET /animals` - List animals
- `POST /animals` - Create animal
- `GET /animals/{id}` - Get animal details
- `PUT /animals/{id}` - Update animal
- `DELETE /animals/{id}` - Delete animal
- `GET /animals/{id}/timeline` - Get animal events
- `GET /animals/{id}/pedigree` - Get pedigree data
- `POST /litters` - Create litter
- `POST /litters/{id}/whelp` - Log whelping
- `GET /contacts` - List contacts
- `POST /contacts` - Create contact
- `POST /animals/{id}/events` - Add event
- `POST /animals/{id}/generate-handover` - Generate PDF

## Contributing

This plugin is designed for professional breeders. Feature requests and bug reports are welcome.

## License

GPL v2 or later

## Support

For support, please check the WordPress plugin repository or contact the developer.

---

**TruePaws** - Your data, your server, your rules. Automated peace of mind for professional breeders.