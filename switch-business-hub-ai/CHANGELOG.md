# Changelog

All notable changes to Switch Business Hub AI will be documented in this file.

## [2.1.0] - 2026-02-18

### 🚀 AI Quote Flow Overhaul

- Rebuilt guided AI conversation for print/signage/design quoting
- Added wedding welcome board guidance with A1 size/material options (Correx, Forex, Perspex)
- Added custom-job fallback estimates when a product is not in the catalog
- AI now asks design-vs-own-file, delivery, due date and notes before finalizing
- Quote payload now carries richer item detail (SKU, design fee, delivery fee, file note)

### 🛍️ Product + Pricing Improvements

- Expanded catalog with additional print products and wedding signage options
- Added baseline 75% markup normalization where cost prices are available
- Added core service items:
  - Design service from R350
  - Custom jobs at R350/hour
  - Delivery from R160
- Added product image resolver so admin-assigned product images display in shop cards

### 📦 Order / Invoice / Tracking Fixes

- Fixed shop invoice creation to respect quantity correctly
- Updated invoice numbering to `INV-SBH0001` format
- Tracking now works by invoice/SBH reference and checks quote/invoice records first
- Improved tracking output for pending vs verifying payment status

### 👤 Customer Portal UX + Reliability

- Header branding now supports full business name + reg/CSD lines
- Top branding text is styled in orange, per brand request
- Contact panel now uses business options for phone/WhatsApp/email/address
- Banking panel colors improved for better visibility
- Quote submission now:
  - uses logged-in customer details automatically
  - includes AI transcript
  - supports optional multi-file upload
- Added chat history persistence endpoints and logged-in sync support

### 🛠️ Admin Enhancements

- Product manager now supports image selection/upload directly in product edit form
- Orders view now shows richer item details (SKU, design/delivery fee breakdown)
- Orders view now displays AI chat transcript when available

## [1.8.0] - 2026-01-13

### 🚀 Major Features

#### Complete AI Rebuild
- **Conversational Quote Builder**: AI guides customers step-by-step
- **Design Service Integration**: AI asks about design needs (+R350)
- **Auto-Fill Quote Form**: All AI-collected info fills the form
- **Chat Transcript Attached**: Conversation attached to quotes

#### Product Catalog with SKUs
- 25+ Product categories with variants
- Each variant has unique SKU (e.g., BC-500-DS)
- 75% markup pricing from wholesale rates
- Design Service: from R350
- Custom Jobs: R350 per hour
- Delivery: from R160

#### Customer Dashboard
- Dashboard with stats for logged-in users
- Quote and invoice history
- Guest users: Direct AI chat access

#### Company Branding
- Full company details in header
- Bank details in Contact tab
- Invoice format: INV-SBH01

### 🔧 Improvements

- ✅ Contact tab: Phone, email, WhatsApp links
- ✅ Track order: Invoice number only (not email)
- ✅ Product images: Upload via Settings > Products
- ✅ Quote form: Auto-fills from AI conversation

### 📦 Products (75% markup)

**Printing:** Business Cards R175-R613, Flyers R140-R788, Posters R61-R1050
**Signage:** Banners R158-R1138, Pull-Ups R613-R1575, X-Banners R490-R665
**Wedding:** Welcome Boards R210-R1488, Seating Charts R315-R1138
**Apparel:** T-Shirts R123-R201, Golf Shirts R210-R315, Hoodies R315-R525
**Vehicle:** Door Decals R1400+, Half Wraps R3500+, Full Wraps R7875+
**Services:** Design R350+, Custom R350/hr, Delivery R160+

---

## [1.7.0] - 2026-01-12
- Initial conversational AI
- WhatsApp integration

## [1.6.0] - 2026-01-11  
- Premium UI redesign
- Service catalog

---
**Switch Graphics (Pty) Ltd** | 068 147 4232 | www.switchgraphics.co.za
