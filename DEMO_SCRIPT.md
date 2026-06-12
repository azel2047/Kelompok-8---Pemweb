# EduAttend (SI-ABSEN-QR) - Short Demo Script (English)

A concise, 3-minute presentation script focusing on core features and technical highlights.

---

### Part 1: Intro & Problem (30s)
* **Visual:** Dashboard showing the title: **EduAttend (SI-ABSEN-QR)**.
* **Speech:**
> "Hello everyone. Today I'm demonstrating **EduAttend**, a smart attendance system designed to eliminate attendance fraud (such as QR screenshot sharing).
> 
> EduAttend uses a dual-verification system:
> 1. **Dynamic QR Codes** generated using Time-based HMAC (SHA-256) signatures that expire every 15 seconds.
> 2. **Real-time Client-side Face ID** using **face-api.js** to prevent proxy attendance."

---

### Part 2: Teacher View - Dynamic QR (45s)
* **Visual:** Filament v3 Admin Panel $\rightarrow$ Navigate to Class Schedule $\rightarrow$ Show the dynamic QR code page. Point out the QR changing/refreshing.
* **Speech:**
> "Here is the Teacher Dashboard built with **Filament v3**. 
> 
> When starting class, the teacher displays this QR code. Notice it automatically changes every 15 seconds. 
> 
> Because of this Time-based HMAC signature, screenshots of this QR are useless. By the time a student shares it, the token has already expired."

---

### Part 3: Student View - Face ID & Scan (60s)
* **Visual:** Student Portal (mobile view) $\rightarrow$ Show face registration modal (camera active, green bounding box) $\rightarrow$ Scan the QR code $\rightarrow$ Face verification pops up $\rightarrow$ Success state.
* **Speech:**
> "Now, onto the Student Portal, built with Livewire and Alpine.js.
> 
> First, students register their Face ID. The browser uses **face-api.js** to detect facial landmarks and convert them into a **128-dimensional embedding vector** stored in our database.
> 
> Next, when the student clicks 'Scan Absen', they scan the teacher's QR code. Once scanned, the front camera immediately opens for face verification.
> 
> The system calculates the **Euclidean Distance** between the scanned face and the registered template. If the distance is $\le 0.6$, the student is instantly marked as 'Hadir' (Present) without page reload."

---

### Part 4: Under the Hood & Conclusion (45s)
* **Visual:** Quick glimpse of code/database diagram $\rightarrow$ Show Filament's Audit Log.
* **Speech:**
> "Under the hood, we use MySQL **Stored Procedures** (`sp_catat_absen_qr`) for fast, secure attendance logging. 
> 
> We also have a database **Trigger** (`tr_after_insert_absensi`) that automatically writes to `audit_logs` every time an attendance record is added.
> 
> With this architecture, EduAttend provides a highly secure, automated, and tamper-proof attendance solution. Thank you!"
