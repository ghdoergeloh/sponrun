export function calcDonation(
    donationPerLap: number,
    staticMax: number,
    laps: number,
): number {
    const donation = donationPerLap * laps;
    if (staticMax === 0) return donation;
    if (donationPerLap === 0) return staticMax;
    return Math.min(donation, staticMax);
}

export function totalDonation(
    sponsors: Array<{ donation_per_lap: string; donation_static_max: string }>,
    laps: number,
): number {
    return sponsors.reduce(
        (sum, s) =>
            sum +
            calcDonation(
                parseFloat(String(s.donation_per_lap).replace(',', '.')),
                parseFloat(String(s.donation_static_max).replace(',', '.')),
                laps,
            ),
        0,
    );
}

export const fmt = (v: number) =>
    v.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
