<?php

namespace Parables\Geo;

it('says hello', function () {
    expect('hello')->toBe('hello');
});

test('GeoName::fromLine returns an instance of GeoName', function () {
    $line = "2300660	Republic of Ghana	Republic of Ghana	Gaana,Gana,Gana - Ghana,Ganaa,Ganaea,Ganaen,Ganao,Gane,Ganäa,Ganän,Ganë,Gha-na,Gha-na (Ghana),Ghana,Ghana nutome,Ghána,Gold Coast,Gàna,Gána,Kana,Ngana,Nkana,Qana,Republic of Ghana,gana,gana gong he guo,gana jۇmھۇryyyty,ghana,ghna,ghʼanʻ,gʼnh,i-Ghana,il-Ghana,jia na,ka na,kana,khana,prathes kana,qana,Γκάνα,Гана,Գանա,גאנה,גהאַנע,غانا,غنا,غەنا,قانا,ګana,ګانا,گانا,گانا جۇمھۇرىيىتى,گهانا,گھانا,घाना,গানা,ঘানা,ਘਾਨਾ,ઘાના,ଘାନା,கானா,ఘనా,ಘಾನಾ,ഖാന,ഘാന,ඝානාව,กานา,กาน่า,ประเทศกานา,ການາ,ཀ་ན།,ག་ན,གྷ་ན,གྷ་ན།,ဂါနာ,განა,ጋና,ហគាណា,ហ្គាណា,ガーナ,ガーナ共和国,加纳,迦納,가나	8.1	-1.2	A	PCLI	GH		00				29767108		137	Africa/Accra	2021-08-16";
    $geoName = GeoName::fromLine(line: $line);
    expect($geoName)->toBeInstanceOf(GeoName::class);
    expect($geoName->id())->toBe("2300660");
    expect($geoName->name())->toBe("Republic of Ghana");
    expect($geoName->asciiName())->toBe("Republic of Ghana");
    expect($geoName->alternateNames())->toBe("Gaana,Gana,Gana - Ghana,Ganaa,Ganaea,Ganaen,Ganao,Gane,Ganäa,Ganän,Ganë,Gha-na,Gha-na (Ghana),Ghana,Ghana nutome,Ghána,Gold Coast,Gàna,Gána,Kana,Ngana,Nkana,Qana,Republic of Ghana,gana,gana gong he guo,gana jۇmھۇryyyty,ghana,ghna,ghʼanʻ,gʼnh,i-Ghana,il-Ghana,jia na,ka na,kana,khana,prathes kana,qana,Γκάνα,Гана,Գանա,גאנה,גהאַנע,غانا,غنا,غەنا,قانا,ګana,ګانا,گانا,گانا جۇمھۇرىيىتى,گهانا,گھانا,घाना,গানা,ঘানা,ਘਾਨਾ,ઘાના,ଘାନା,கானா,ఘనా,ಘಾನಾ,ഖാന,ഘാന,ඝානාව,กานา,กาน่า,ประเทศกานา,ການາ,ཀ་ན།,ག་ན,གྷ་ན,གྷ་ན།,ဂါနာ,განა,ጋና,ហគាណា,ហ្គាណា,ガーナ,ガーナ共和国,加纳,迦納,가나");

    expect($geoName->latitude())->toBe("8.1");
    expect($geoName->longitude())->toBe("-1.2");
    expect($geoName->featureClass())->toBe("A");
    expect($geoName->featureCode())->toBe("PCLI");
    expect($geoName->countryCode())->toBe("GH");
    expect($geoName->cc2())->toBe("");
    expect($geoName->admin1Code())->toBe("00");
    expect($geoName->admin2Code())->toBe("");
    expect($geoName->admin3Code())->toBe("");
    expect($geoName->admin4Code())->toBe("");
    expect($geoName->population())->toBe("29767108");
    expect($geoName->elevation())->toBe("");
    expect($geoName->dem())->toBe("137");
    expect($geoName->timezone())->toBe("Africa/Accra");
    expect($geoName->modificationDate())->toBe("2021-08-16");
});
