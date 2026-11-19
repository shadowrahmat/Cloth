// Bengali number / currency formatting for the storefront.
const BN_DIGITS = '০১২৩৪৫৬৭৮৯';

export const bnNumber = (n) => String(n ?? '').replace(/\d/g, (d) => BN_DIGITS[d]);

/** ৳ ১,২৫০ */
export const taka = (v) => '৳ ' + bnNumber(Math.round(Number(v || 0)).toLocaleString('en-IN'));
