<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of {{ ucfirst($language) }} Competency</title>
    @include('layouts.reviewer.certificationCSS')
    
</head>
<body>

<div class="paper">
        <div class="bg-pattern"></div>
        
        <div class="frame">
            <div class="content-wrapper">
                
                <div class="badge">Official Document</div>
                
                <h1>Certificate of {{ strtoupper($language) }} Competency</h1>
                <div class="sub-title">Reviewer Qualification Award</div>

                <div class="certifies-text">This certifies that</div>

                <div class="name">{{ $name }}</div>
                
                <div class="divider"></div>

                <div class="body-text">
                    Has successfully completed the {{ $language }} competency test and has demonstrated strong proficiency. 
                    They have met all requirements of the program and are now qualified to review the content.
                </div>

                <div class="skill-box">
                    <div class="skill-label">Verified Language Specialization</div>
                    <div class="skill-name">{{ strtoupper($language) }}</div>
                </div>

                <div class="cert-footer">
                    <div class="cert-id">CID: {{ $certificate_id }}</div>
                </div>

                <div>
                    <h2>on {{ $date }}</h2>
                </div>

            </div>

            

        </div>
    </div>

</body>
</html>