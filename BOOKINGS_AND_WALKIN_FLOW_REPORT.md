# YP4K Resort — Booking, Reservation & Walk-In Flow Diagnostic Report (Casual Tagalog / Taglish Edition)

**Petsa:** Setyembre 28, 2026  
**Scope:** Online Booking ng User, Admin Dashboard, Staff Front-Desk, at Walk-In POS  
**Status:** Scan lang 'to, pre! Walang ginalaw o binagong code sa system mo.

---

## 1. Quick Summary (Ano ba talagang problema?)

Pre, pagkatapos nating i-scan yung buong codebase ng YP4K Resort, ang pinaka-ugat ng sakit ng ulo sa system ay **nagbabanggaan yung dalawang magkaibang booking system**:

1. **Yung Lumang Flow ("Bayad Agad Bago Submit")**: 
   Dito, kailangan mag-upload agad ng screenshot ng GCash bago ma-submit yung booking. Diretsong nababawas yung slot.
2. **Yung Bagong Flow ("Approve Muna Bago Bayad — 2-Hour Window")**: 
   Dito naman, magsu-submit lang ng request si guest nang walang bayad (`pending_review`). Pag in-approve ni Admin, magiging `awaiting_payment` at bibigyan si guest ng **2 hours** para mag-send ng GCash receipt. Pag na-upload, magiging `payment_verifying`, at pag kinumpirma ni Admin, magiging `approved` na bayad na.

**Bakit nagkakagulo?** Kasi may mga pages na lumipat na sa bago, pero may mga files at endpoints na naiwan sa luma! Kaya ang labas:
- **May mga cottage na nagiging ₱0.00** kapag walk-in (libre ang laro!).
- **Naaagawan ng kwarto** yung mga online guests habang nagbabayad pa lang sa GCash.
- **Nganga yung staff** sa dashboard nila kasi puro "View" lang ang button, bawal mag-approve o mag-confirm ng bayad.
- **Drawing yung Cancel button** sa User Dashboard — nawawala lang sa screen pero nandun pa rin sa database.
- **Iba-iba ang tinitignan ng mga kalendaryo**, kaya may calendar na berde (available) pero puno na pala sa totoo.

---

## 2. Diagram ng Gulo sa System

```
       USER DASHBOARD                   ADMIN DASHBOARD                  STAFF DASHBOARD
  ┌─────────────────────────┐      ┌─────────────────────────┐      ┌─────────────────────────┐
  │ create-booking.php      │      │ bookings.php            │      │ bookings.php            │
  │ - Rooms & Activities    │      │ - May Approve button    │      │ - "View" button lang    │
  │ - Walang bayad upfront  │      │ - May Confirm Pay       │      │ - Walang pang-approve   │
  │ - Status: pending_review│      │ - May countdown timer   │      │ - Walang pang-confirm   │
  └───────────┬─────────────┘      └───────────▲─────────────┘      └───────────┬─────────────┘
              │                                │                                │
              ▼                                │                                │
      [ Database: bookings ] ──────────────────┴────────────────────────────────┘
              ▲
              │                    ┌─────────────────────────┐      ┌─────────────────────────┐
              ├────────────────────┤ walkin_pos.php          │      │ walkin.php              │
              │                    │ - Walk-in POS           │      │ - Walk-in POS           │
              │                    │ - May ₱0 day-tour bug   │      │ - May ₱0 day-tour bug   │
              │                    │ - Status: checked_in    │      │ - Status: checked_in    │
              │                    └─────────────────────────┘      └─────────────────────────┘
              │
  ┌───────────┴─────────────┐
  │ bundle-booking.php      │
  │ - Bundle Packages       │
  │ - Pinipilit magbayad    │
  │   upfront (nagka-clash  │
  │   sa 2-hour window)     │
  └─────────────────────────┘
```

---

## 3. Mga Senaryo sa Tunay na Buhay (Para mas madaling ma-gets)

### Senaryo 1: Libreng Cottage sa Walk-In (₱0.00 Glitch)
* **Saan sa code:** `php/process_walkin_booking.php` (Linya 70–73 & 100–102)
* **Ang Eksena:**
  1. May pamilyang dumating sa resort, trip nilang mag-swimming for the day at umupa ng **Umbrella Cottage (8 Pax+)**.
  2. Binuksan ni Staff yung Walk-In screen sa counter (`Staff_Dashboard/walkin.php`). Nakalagay sa screen: `₱500.00`.
  3. Tinype ni Staff pangalan ng guest at pinindot ang **"Confirm & Check-In"**.
  4. Pero sa likod ng code, ito pala yung hinanap sa database:
     ```php
     SELECT id, name, price_per_night AS price_per_hour FROM accommodations WHERE id = ?
     ```
  5. Eh dahil pang-Day Tour lang yung Umbrella Cottage, ang nakalagay sa `price_per_night` nito sa database ay **`0.00`** (kasi nasa `price_per_day` yung totoong ₱500).
  6. Kinompyut tuloy ng backend: `0.00 x 1 = ₱0.00`!
  7. Na-save sa database na bayad na raw (`paid`), at lumabas sa resibo: **`Total: ₱0.00 | CASH`**.
  8. **Boom:** Nakalibre ng cottage yung guest nang di namamalayan ni staff kung bakit naging zero!

---

### Senaryo 2: Naagawan si Online Guest Habang Nagbabayad sa GCash (Walk-In Overbooking)
* **Saan sa code:** `php/process_walkin_booking.php` (Linya 133–147)
* **Ang Eksena:**
  1. Si Juan nag-book online ng VIP Room para bukas.
  2. In-approve ni Admin yung request, kaya naging `awaiting_payment` at binigyan si Juan ng 2 oras para mag-bayad sa GCash.
  3. Habang binubuksan pa lang ni Juan yung GCash app niya sa bahay, may walk-in na dumating sa resort desk naghahanap din ng VIP Room bukas.
  4. Binuksan ni Staff yung Walk-In POS. Nag-check yung POS kung bakante pa ba yung room gamit ang query na 'to:
     ```sql
     WHERE bs.name IN ('pending_review', 'approved', 'checked_in')
     ```
  5. **Wala sa listahan yung `awaiting_payment`!**
  6. Para sa system, bakante pa raw yung VIP Room! Binigay agad ni Staff yung room sa walk-in at minarkahang `checked_in`.
  7. Pagkalipas ng 10 minutes, nag-send si Juan ng ₱4,000 sa GCash at nag-upload ng screenshot online.
  8. **Boom:** Dalawa na silang nakabayad sa iisang kwarto. Magkakagulo sa front desk bukas pagdating nila pareho.

---

---

### Senaryo 4: Drawing na Cancel Button sa My Bookings (Nawala sa Screen Pero Andun Pa Rin sa DB)
* **Saan sa code:** `User_Dashboard/my-bookings.php` (Linya 500–525)
* **Ang Eksena:**
  1. Nagbago ang isip ng customer, gusto niyang i-cancel yung approved booking niya.
  2. Binuksan niya yung `User_Dashboard/my-bookings.php`, nakita niya yung pulang button na **"Cancel Booking"**, at pinindot niya.
  3. May lumabas na pop-up confirmation: *"Are you sure?"* Pinindot niya yung "Yes, Cancel".
  4. Nag-fade out yung card, lumabas yung toast notification na *"Booking cancelled and refunded"*, at nawala sa screen. Akala ni customer, tapos na.
  5. **Ang totoo:** Walang pinadalang request sa database! Tinanggal lang ng JavaScript yung card sa screen gamit ang:
     ```javascript
     bookingCard.remove(); // Visual effect lang pala!
     ```
  6. Hindi tinawag yung `php/cancel_booking.php`. Pag nag-refresh si customer ng browser, andun pa rin yung booking!
  7. **Boom:** Sa side ng resort, akala niyo darating pa rin yung guest kaya naka-block pa rin yung kwarto.

---

### Senaryo 5: Double Booking Kapag Mabilis Mag-Click si Admin
* **Saan sa code:** `php/update_booking_status.php` (Linya 70–120)
* **Ang Eksena:**
  1. Dahil hindi binablock ng form kapag may nakabinbin pa lang na request, nakapag-submit sina Pedro at Maria para sa nag-iisang Grand Villa sa darating na Sabado.
  2. Pareho silang pumasok bilang `pending_review`.
  3. Binuksan ni Admin yung dashboard. Nakita niya yung request ni Pedro, pinindot niya yung "Approve". Naging `awaiting_payment` si Pedro.
  4. Dahil marami pang inaasikaso, nakita din ni Admin yung request ni Maria, at na-click din niya yung "Approve".
  5. Sa loob ng backend code, **wala kasing double-check kung may naaprubahan na bang iba sa petsang yun bago mag-approve**. Diretso lang itong nag-update ng database!
  6. **Boom:** Parehong nakatanggap ng email sina Pedro at Maria na approved na raw sila at magbayad within 2 hours. Pareho silang nag-GCash para sa iisang villa.

---



## 4. Talaan ng Magkakaibang Status sa Availability (Bakit Labo-Labo ang mga Calendar?)

Ito ang dahilan kung bakit minsan sa calendar berde pa, pero pag cliniclick, bawal na pala: **Iba-iba ang tinitignang status ng bawat file sa code!**

| File / API | Para Saan? | Mga Status na Binibilang na "Occupied" | Ang Problema / Kulang |
| :--- | :--- | :--- | :--- |
| **`check_availability.php`** | Checking habang pumipili ng date | `pending_review`, `approved` | Deadma sa `awaiting_payment`, `payment_verifying`, at `checked_in`! |
| **`upload_payment_proof.php`** | Pag nagsu-submit ng online booking | `awaiting_payment`, `payment_verifying`, `approved`, `checked_in` | Deadma sa `pending_review` (kaya nakakapag-doble ng submit) |
| **`process_walkin_booking.php`**| Pag-save ng Walk-In sa counter | `pending_review`, `approved`, `checked_in` | Deadma sa `awaiting_payment` at `payment_verifying` (pwedeng maagaw ng walk-in) |
| **`get_bundle_availability.php`**| Calendar sa bundle packages | `pending_review`, `approved`, `checked_in` | Deadma sa `awaiting_payment` at `payment_verifying` |
| **`fetch-calendar-data.php`** | Interactive calendar sa booking form | `awaiting_payment`, `payment_verifying`, `approved`, `checked_in` | Deadma sa `pending_review` |
| **`get_calendar_data.php`** | Calendar ni Admin at Staff | Lahat maliban sa `rejected` at `cancelled` | Binibilang lahat, kaya laging iba ang kulay sa admin kaysa sa user! |

---

## 5. Cheat Sheet ng mga Problema sa Code (Audit Table)

| ID | Issue | Saang File? | Linyang May Tama | Gaano Kalala? | Ano ang Epekto? |
| :---: | :--- | :--- | :---: | :---: | :--- |
| **01** | Walk-In Presyo | `php/process_walkin_booking.php` | 71, 101 | **CRITICAL** | Nagiging ₱0.00 ang day-tour cottages dahil `price_per_night` ang binabasa imbes na `price_per_day`. |
| **02** | Walk-In Conflict | `php/process_walkin_booking.php` | 140 | **HIGH** | Hindi binibilang ang `awaiting_payment`, kaya naaagaw ng walk-in ang kwarto ng online guest na nagbabayad pa lang. |
| **03** | Room Capacity | `php/process_walkin_booking.php` | 155–168 | **HIGH** | Kapasidad ng tao (pax) ang binabasa imbes na bilang ng kwarto (`daily_booking_limit = 1`). |
| **04** | Staff Controls | `Staff_Dashboard/bookings.php` | 305–310 | **HIGH** | Walang Approve, Reject, o Confirm Pay buttons sa table ng Staff. "View" lang ang pwede. |
| **05** | Permissions | `php/update_booking_status.php` | 13–20 | **MEDIUM** | Admin session lang ang tinatanggap. Nala-log bilang `'admin'` kahit si Staff ang gumawa. |
| **06** | Double Approval | `php/update_booking_status.php` | 70–100 | **HIGH** | Walang checking kung may naaprubahan nang iba bago mag-approve papuntang `awaiting_payment`. |
| **07** | Drawing na Cancel | `User_Dashboard/my-bookings.php` | 500–525 | **HIGH** | Sa screen lang nabubura ang card. Hindi tinatawag ang server kaya nananatiling active sa database. |
| **08** | Orphaned API | `php/cancel_booking.php` | 1–142 | **MEDIUM** | May maayos na cancel endpoint pero walang tumatawag sa kanya mula sa frontend. |
| **09** | Bundle Clash | `User_Dashboard/bundle-booking.php` | 1370–1415 | **MEDIUM** | Hinihingan agad ng resibo sa umpisa, pero tinatarget pa rin ng 2-hour payment demand email pagka-approve. |
| **10** | Tago ang Bundle | `User_Dashboard/create-booking.php` | 947 | **LOW** | Naka-`display: none` ang bundle pill at walang link sa navigation menu papuntang bundle page. |
| **11** | Walk-In Logout | `Admin_Dashboard/bookings.php` | 340–385 | **MEDIUM** | Walang "Check-Out" button para sa `checked_in` na mga walk-in, kaya hindi na sila natatapos sa system. |
| **12** | Dead Code | `php/create_booking.php` | 1–438 | **LOW** | Lumang script na hindi na ginagamit pero nakatambak pa rin sa folder. |

---

## 6. Paano Natin Ito Aayusin? (Game Plan)

Kapag go ka na para ipaayos 'to, ito ang pinaka-smooth na diskarte:

1. **Pag-isahin ang "Occupied" Query sa Lahat ng Files**:
   Gagawin nating pare-pareho ang binibilang nilang status:
   `('pending_review', 'awaiting_payment', 'payment_verifying', 'approved', 'checked_in')`. Para pag may nag-book, pare-parehong blocked sa lahat ng kalendaryo.
2. **Ayusin ang Singil sa Walk-In POS**:
   I-update ang `process_walkin_booking.php` para basahin ang `price_per_day` kapag day-tour accommodation para hindi nagiging ₱0.00.
3. **Gawing Totoo ang Cancel Button ni User**:
   I-connect ang `cancelBooking()` sa `php/cancel_booking.php` para totoong ma-cancel sa database at bumalik sa kalendaryo yung slot.
4. **Lagyan ng Action Buttons ang Staff Dashboard**:
   Kopyahin ang buttons mula sa Admin table papunta sa Staff table para makapag-approve at makapag-confirm ng bayad si Staff kahit wala si Admin sa tabi niya.
5. **I-align ang Bundle Booking**:
   Gawing "Approve First, Pay Later" din ang Bundle para pareho ng Rooms at Activities, O kaya kung magbabayad agad, i-tag agad bilang `payment_verifying` para rekta confirm na lang si Admin at hindi na hihingan ulit si guest ng 2-hour payment.
6. **Maglagay ng "Check Out" Button para sa Walk-In**:
   Para pag umalis na yung walk-in guest, mapindot ni Staff o Admin ang "Check Out" at malipat sa `completed`.
