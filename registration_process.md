# Redesign Plan: Conference Registration Privileges & Pricing UI

## Problem & Background
Previously, all 5 registration categories across Early Bird and Regular tiers had 7 identical benefits repeatedly printed inside each card:
1. Access to all Keynote & Technical Sessions
2. Official Presentation / Participation Certificate
3. Paper Presentation Slot (Onsite / Online)
4. Conference Kit, Badge & Program Book
5. Networking Lunch & Refreshments
6. Consideration for Scopus-indexed Q2 Journal Publication
7. Access to Co-located Workshops & Exhibitions

This repetition bloated the cards, made comparing rates tedious, and looked visually cluttered and amateurish. The user requested a modern, executive UI that unifies the common perks into a dedicated showcase and presents the fee structure clearly.

---

## Proposed Design Architecture

### 1. Unified Entitlements Showcase ("All-Inclusive Privileges")
- A dedicated, full-width executive showcase container at the top of `#buy-tickets`.
- Clear header announcing: **"Every Registration Package Includes 100% Full Conference Privileges"**.
- Modern 7-card responsive grid highlighting each amenity with custom icons, badges, and explanatory text:
  - 🎙️ Keynotes & Technical Tracks
  - 📜 Official Certificates (Presentation & Participation)
  - 💻 Hybrid Presentation Slot (Onsite / Virtual)
  - 🎒 Conference Kit, Badge & Abstract Book
  - 🍽️ Banquet Lunch & Refreshments
  - ⭐ Scopus Q2 Journal Publication Review
  - 🔬 Co-located Workshops & Exhibition Access
- Reassures attendees that no matter their tier (Student, Academic, Industry, SAARC, International), they receive complete, unrestricted conference access.

### 2. Streamlined Rate Selector & Modern Pricing Views
- **Pill/Tab Navigation**:
  - `[ Early Bird Registration (Special Rate) ]`
  - `[ Regular / Late Registration ]`
  - `[ Complete Fee Structure (Comparison Matrix Table) ]`
- **Sleek Pricing Cards (Early Bird & Regular)**:
  - Focused strictly on category distinction: Icon, Category Badge, Target Demographic, Prominent Price (`৳ 4,000` / `US$ 150`), Savings Tag (`Save ৳ 1,000`), and quick CTA button.
  - No repeated 7-bullet point lists inside cards.
- **Executive Fee Comparison Matrix (Table View)**:
  - High-demand academic format summarizing all 5 categories side-by-side with Early Bird, Regular Rate, Savings, Currency, and Register action.

### 3. Guidelines & Payment Info Strip
- Mini cards covering Payment Gateways (bKash, Nagad, Visa/Mastercard/SWIFT), Presentation Modes (Onsite at Daffodil Smart City / Online Zoom), and Publication Guidelines.

---

## Files to Modify
- `resources/views/main/sections/buy_ticket.blade.php`: Complete redesign with modern HTML and scoped CSS in `@push('style')`.

## Verification Plan
1. Test web server response: `curl -s http://icmria.local/` (HTTP 200).
2. Validate responsive markup on desktop, tablet, and mobile viewports.
3. Test modal and registration button links.
