const COOKIE = "gosolo_session";

export function readSessionCookie() {
  if (typeof document === "undefined") return null;
  const match = document.cookie.match(/(?:^|; )gosolo_session=([^;]+)/);
  return match ? decodeURIComponent(match[1]) : null;
}

export function writeSessionCookie(userId: string | null) {
  if (typeof document === "undefined") return;
  if (!userId) {
    document.cookie = `${COOKIE}=; path=/; max-age=0; samesite=lax`;
    return;
  }
  document.cookie = `${COOKIE}=${encodeURIComponent(userId)}; path=/; max-age=2592000; samesite=lax`;
}

export async function hashPassword(password: string) {
  const data = new TextEncoder().encode(`gosolo-demo:${password}`);
  const digest = await crypto.subtle.digest("SHA-256", data);
  return [...new Uint8Array(digest)].map((byte) => byte.toString(16).padStart(2, "0")).join("");
}

export function createId() {
  return crypto.randomUUID();
}

const STORAGE_KEY = "gosolo.demo.v1";

export function readStorage<T>(fallback: T): T {
  if (typeof window === "undefined") return fallback;
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY);
    if (!raw) return fallback;
    return JSON.parse(raw) as T;
  } catch {
    return fallback;
  }
}

export function writeStorage(value: unknown) {
  if (typeof window === "undefined") return;
  window.localStorage.setItem(STORAGE_KEY, JSON.stringify(value));
}

export function clearStorage() {
  if (typeof window === "undefined") return;
  window.localStorage.removeItem(STORAGE_KEY);
}
