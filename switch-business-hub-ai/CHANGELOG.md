# Changelog

## [3.0.0] - 2026-02-18

### Major Rewrite
- **AI Chatbot**: Completely rewritten as conversational graphic design expert
  - Step-by-step guidance: product → options → quantity → design needs → purpose → delivery → notes
  - Asks if customer needs design service (R350+) or has own file
  - Shows estimated pricing throughout conversation
  - Understands print industry terminology (correx, perspex, DL, pull-up, etc.)
  - Works for logged-in AND non-logged-in users
  - Chat transcript attached to quotes for admin review
  
- **Product Catalog**: 60+ products with 75% markup
  - Sourced from Vistaprint, Printulu, ImpressWeb, MediaMafia
  - Design service from R350, custom hourly R350/hr
  - Delivery from R160
  - Every variation has its own price and SKU
  - Selling descriptions for each product
  - Categories: Design, Business Cards, Flyers, Brochures, Posters, Banners, Signage, Wedding & Events, Stickers, Apparel, Vehicle, Corporate Gifts, Canvas, Books, Event Displays, Stamps, Websites, Services

- **Shop**: Full e-commerce functionality
  - Product variations with add to cart
  - Cart with checkout flow
  - Invoice generation with SBH prefix
  - File upload support (any format, no size limit)
  - Product images uploadable from admin

- **Branding**: Switch Graphics (Pty) Ltd
  - Orange header text
  - Registration number and CSD number displayed
  - Brighter banking details with blue theme
  - WhatsApp button on contact tab

- **Invoicing**: SBH prefix (SBH0001, SBH0002...)
  - Quotes: QT-SBH0001
  - Track by invoice number only (not email)

- **Authentication**: Fixed
  - Login/Register within plugin (no WordPress redirect)
  - Email required for registration
  - WhatsApp number as primary contact
  - Logout button on dashboard

- **User Dashboard**
  - Separate tabs for Orders/Invoices and Quotes
  - View order status, items, and totals
  - Upload payment proof
  - Chat with AI for inquiries

- **Contact Tab**
  - WhatsApp chat button
  - Call, Email, Location cards
  - Banking details with blue gradient theme

## [2.0.0] - 2026-01-17
- Added shop with products
- Added order management
- Added portfolio section

## [1.8.2] - 2026-01-13
- Initial release with AI chat
- Basic quote submission
- Customer portal
