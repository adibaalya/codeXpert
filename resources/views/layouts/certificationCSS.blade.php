<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">

<style>
    /* 1. LOCK PDF DIMENSIONS */
    @page {
            size: A4 landscape;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #fff; /* Changed to white for printing safety */
            font-family: 'Montserrat', sans-serif;
            /* REMOVED display: flex; - This breaks PDF generators like DOMPDF */
        }

        /* 2. THE PAPER */
        .paper {
            width: 100%; /* Use 100% of the A4 page defined above */
            height: 100%;
            position: relative;
            /* box-shadow doesn't render well in some PDFs, removed for safety */
        }

        /* 3. BACKGROUND DECORATION */
        .bg-pattern {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            /* Radial gradients can be heavy for PDFs, ensure your generator supports it */
            background-color: #f8f9fa; 
            opacity: 1;
            z-index: -1;
        }

        /* 4. THE FRAME */
        .frame {
            position: absolute; 
            top: 15mm;
            left: 15mm;
            right: 15mm;
            bottom: 15mm;
            border: 8px solid #0f172a; /* Navy */
            background: #ffffff; /* Removed alpha transparency for PDF safety */
            text-align: center;
        }

        /* 5. CONTENT POSITIONING */
        .content-wrapper {
            position: relative;
            top: 40px; /* Adjusted for better spacing without flex */
            width: 100%;
        }

        .badge {
            background: #0f172a;
            color: #c49a6c;
            padding: 8px 25px;
            border-radius: 20px; /* Reduced radius for better PDF support */
            text-transform: uppercase;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            display: inline-block;
            margin-bottom: 20px;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 38px;
            color: #0f172a;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        h2 {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            color: #0f172a;
            margin: 40px 0 0 0;
        }

        .sub-title {
            color: #64748b;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 30px;
        }

        .certifies-text {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            font-size: 16px;
            color: #555;
            margin-bottom: 10px;
        }

        .name {
            font-family: 'Playfair Display', serif;
            font-size: 50px; /* Slightly reduced to prevent wrapping */
            color: #c49a6c;
            font-weight: 700;
            margin: 0 0 10px 0;
            line-height: 1.2;
        }

        .divider {
            height: 2px;
            width: 150px;
            background: #e2e8f0;
            margin: 0 auto 20px auto;
        }

        .body-text {
            font-size: 14px;
            line-height: 1.6;
            color: #334155;
            width: 80%; /* Percentage width is safer than mm for text containers */
            margin: 0 auto 30px auto;
        }

        .skill-box {
            border: 1px solid #c49a6c;
            background: #fffaf0;
            padding: 10px 40px;
            display: inline-block;
        }

        .skill-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #999;
            letter-spacing: 1px;
        }

        .skill-name {
            font-size: 20px;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: #0f172a;
        }

        /* FOOTER POSITIONING */
        .cert-footer {
            position: absolute;
            bottom: 100px;
            width: 100%;
            text-align: center;
            left: 0;
        }

        .cert-id {
            font-size: 10pt;
            color: #777;
            font-family: 'Courier New', Courier, monospace;
            letter-spacing: 1px;
        }

    @media print {
        body { background: white; margin: 0; display: block; }
        .paper { box-shadow: none; margin: 0; page-break-after: always; }
    }
</style>