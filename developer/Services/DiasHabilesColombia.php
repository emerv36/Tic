<?php
// developer/Services/DiasHabilesColombia.php
// Cálculo de días hábiles en Colombia: lunes a viernes, excluyendo festivos nacionales
// (fijos, trasladables por Ley Emiliani - Ley 51 de 1983 - y los que dependen de la Pascua).

class DiasHabilesColombia {
    private static $cacheFestivos = [];

    /**
     * Devuelve los festivos del año como mapa ['Y-m-d' => true].
     */
    public static function festivos($anio) {
        $anio = (int)$anio;
        if (isset(self::$cacheFestivos[$anio])) {
            return self::$cacheFestivos[$anio];
        }

        $fechas = [];

        // Festivos fijos (no se trasladan)
        foreach (['01-01', '05-01', '07-20', '08-07', '12-08', '12-25'] as $md) {
            $fechas[] = new DateTimeImmutable("$anio-$md");
        }

        // Festivos trasladables al lunes siguiente (Ley Emiliani)
        foreach (['01-06', '03-19', '06-29', '08-15', '10-12', '11-01', '11-11'] as $md) {
            $fechas[] = self::siguienteLunes(new DateTimeImmutable("$anio-$md"));
        }

        // Festivos que dependen del Domingo de Pascua
        $pascua = self::domingoPascua($anio);
        $fechas[] = $pascua->modify('-3 days'); // Jueves Santo
        $fechas[] = $pascua->modify('-2 days'); // Viernes Santo
        $fechas[] = self::siguienteLunes($pascua->modify('+39 days')); // Ascensión del Señor
        $fechas[] = self::siguienteLunes($pascua->modify('+60 days')); // Corpus Christi
        $fechas[] = self::siguienteLunes($pascua->modify('+68 days')); // Sagrado Corazón

        $mapa = [];
        foreach ($fechas as $fecha) {
            $mapa[$fecha->format('Y-m-d')] = true;
        }

        self::$cacheFestivos[$anio] = $mapa;
        return $mapa;
    }

    public static function esFestivo(DateTimeInterface $fecha) {
        $festivos = self::festivos((int)$fecha->format('Y'));
        return isset($festivos[$fecha->format('Y-m-d')]);
    }

    public static function esDiaHabil(DateTimeInterface $fecha) {
        $diaSemana = (int)$fecha->format('N'); // 1 = lunes ... 7 = domingo
        return $diaSemana <= 5 && !self::esFestivo($fecha);
    }

    /**
     * Devuelve la fecha (a las 00:00) del N-ésimo día hábil posterior a $desde.
     * El día de $desde no se cuenta.
     */
    public static function sumarDiasHabiles(DateTimeInterface $desde, $dias) {
        $fecha = (new DateTimeImmutable($desde->format('Y-m-d')));
        $contados = 0;
        while ($contados < (int)$dias) {
            $fecha = $fecha->modify('+1 day');
            if (self::esDiaHabil($fecha)) {
                $contados++;
            }
        }
        return $fecha;
    }

    /**
     * Indica si ya transcurrieron completos $dias días hábiles desde $desde hasta $ahora.
     * El plazo vence al terminar el último día hábil del conteo.
     */
    public static function plazoVencido(DateTimeInterface $desde, $dias, DateTimeInterface $ahora) {
        $ultimoDia = self::sumarDiasHabiles($desde, $dias);
        return $ahora->format('Y-m-d') > $ultimoDia->format('Y-m-d');
    }

    private static function siguienteLunes(DateTimeImmutable $fecha) {
        $diaSemana = (int)$fecha->format('N');
        if ($diaSemana === 1) {
            return $fecha;
        }
        return $fecha->modify('+' . (8 - $diaSemana) . ' days');
    }

    /**
     * Domingo de Pascua (algoritmo anónimo gregoriano), sin depender de ext-calendar.
     */
    private static function domingoPascua($anio) {
        $a = $anio % 19;
        $b = intdiv($anio, 100);
        $c = $anio % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $mes = intdiv($h + $l - 7 * $m + 114, 31);
        $dia = (($h + $l - 7 * $m + 114) % 31) + 1;
        return new DateTimeImmutable(sprintf('%04d-%02d-%02d', $anio, $mes, $dia));
    }
}
