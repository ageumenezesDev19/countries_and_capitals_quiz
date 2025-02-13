<div class="border border-primary rounded-5 p-3 text-center fs-3 mb-3">
    Pergunta: <span class="text-info fw-bolder">{{ $currentQuestion + 1 }} / {{ $totalQuestions }}</span>
</div>

<div class="text-center fs-3 mb-3">
    Qual é a capital <span id="country-name">{{ $country }}</span>?
</div>

<script>
    const country = "{{ $country }}";
    const countryElement = document.getElementById("country-name");

    function getPreposition(countryName) {
        const countryLower = countryName.toLowerCase();
        const countryWords = countryLower.split(' ');

        if (countryWords.length > 1 && (countryLower.includes(' do ') || countryLower.includes(' da '))) {
            if (countryLower.includes("africa do sul")) {
                return 'da';
            }
            return 'da';
        }

        switch (true) {
            case (countryLower.endsWith('a') || countryLower.endsWith('ã')):
                return 'da';

            case (countryLower.endsWith('o') || countryLower.endsWith('e')):
                return 'do';

            case ('aeiou'.includes(countryLower.charAt(0))):
                return 'da';

            default:
                return 'de';
        }
    }

    const preposition = getPreposition(country);

    countryElement.innerHTML = `${preposition} ${country}`;
</script>
