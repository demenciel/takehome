/**
 * PHP bcmath-compatible arithmetic (truncate toward zero at the given scale).
 * Used so TypeScript money math matches Laravel's bcmul/bcdiv/bcadd/bcsub/bccomp.
 */

function normalize(value: string): { neg: boolean; int: bigint; scale: number } {
  let raw = value.trim();
  let neg = false;

  if (raw.startsWith('+')) {
    raw = raw.slice(1);
  }

  if (raw.startsWith('-')) {
    neg = true;
    raw = raw.slice(1);
  }

  if (raw === '' || raw === '.') {
    return { neg: false, int: 0n, scale: 0 };
  }

  const [whole = '0', frac = ''] = raw.split('.', 2);
  const digits = `${whole.replace(/^0+(?=\d)/, '') || '0'}${frac}`;
  const scale = frac.length;

  return { neg: neg && digits !== '0'.repeat(digits.length), int: BigInt(digits || '0'), scale };
}

function toScaled(value: { int: bigint; scale: number }, scale: number): bigint {
  if (value.scale === scale) {
    return value.int;
  }

  if (value.scale < scale) {
    return value.int * 10n ** BigInt(scale - value.scale);
  }

  return value.int / 10n ** BigInt(value.scale - scale);
}

function format(neg: boolean, int: bigint, scale: number): string {
  const sign = neg && int !== 0n ? '-' : '';

  if (scale === 0) {
    return `${sign}${int.toString()}`;
  }

  const raw = int.toString().padStart(scale + 1, '0');
  const whole = raw.slice(0, -scale) || '0';
  const frac = raw.slice(-scale);

  return `${sign}${whole}.${frac}`;
}

export function bcmul(a: string, b: string, scale: number): string {
  const left = normalize(a);
  const right = normalize(b);
  const product = left.int * right.int;
  const productScale = left.scale + right.scale;
  const truncated = toScaled({ int: product, scale: productScale }, scale);

  return format(left.neg !== right.neg, truncated, scale);
}

export function bcdiv(a: string, b: string, scale: number): string {
  const left = normalize(a);
  const right = normalize(b);

  if (right.int === 0n) {
    throw new Error('Division by zero');
  }

  const shift = scale + right.scale - left.scale;
  const quotient =
    shift >= 0
      ? (left.int * 10n ** BigInt(shift)) / right.int
      : left.int / (right.int * 10n ** BigInt(-shift));

  return format(left.neg !== right.neg, quotient, scale);
}

export function bcadd(a: string, b: string, scale: number): string {
  const left = normalize(a);
  const right = normalize(b);
  const work = Math.max(left.scale, right.scale, scale);
  const leftInt = toScaled(left, work) * (left.neg ? -1n : 1n);
  const rightInt = toScaled(right, work) * (right.neg ? -1n : 1n);
  const sum = leftInt + rightInt;
  const neg = sum < 0n;
  const truncated = toScaled({ int: sum < 0n ? -sum : sum, scale: work }, scale);

  return format(neg, truncated, scale);
}

export function bcsub(a: string, b: string, scale: number): string {
  const right = normalize(b);
  const flipped = right.neg ? b.replace('-', '') : `-${b.replace(/^[+-]/, '')}`;

  return bcadd(a, right.int === 0n ? '0' : flipped, scale);
}

export function bccomp(a: string, b: string, scale: number): number {
  const left = normalize(bcadd(a, '0', scale));
  const right = normalize(bcadd(b, '0', scale));
  const work = Math.max(left.scale, right.scale);
  const leftInt = toScaled(left, work) * (left.neg ? -1n : 1n);
  const rightInt = toScaled(right, work) * (right.neg ? -1n : 1n);

  if (leftInt === rightInt) {
    return 0;
  }

  return leftInt > rightInt ? 1 : -1;
}
