import { createBrowserClient } from "@supabase/ssr";
import type { SupabaseClient } from "@supabase/supabase-js";
import { isSupabaseConfigured } from "@/lib/supabase/env";

let browserClient: SupabaseClient | null = null;

/** Browser client. Session cookies persist across visits. */
export function getSupabase(): SupabaseClient | null {
  if (!isSupabaseConfigured()) return null;
  if (!browserClient) {
    browserClient = createBrowserClient(
      process.env.NEXT_PUBLIC_SUPABASE_URL!,
      process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!,
    );
  }
  return browserClient;
}

export function friendlyAuthError(error: { message: string } | null): string | null {
  if (!error) return null;
  const message = error.message.toLowerCase();
  if (message.includes("invalid login") || message.includes("invalid credentials")) {
    return "That email and password do not match.";
  }
  if (message.includes("already registered") || message.includes("already been registered")) {
    return "An account with that email already exists. Try logging in.";
  }
  if (message.includes("email not confirmed")) {
    return "Confirm your email first, then come back.";
  }
  if (message.includes("password")) {
    return "Use at least 8 characters.";
  }
  if (message.includes("rate limit") || message.includes("too many")) {
    return "That's a lot of tries. Wait a moment, then try again.";
  }
  if (message.includes("network") || message.includes("fetch")) {
    return "We couldn't reach the door. Check your connection and try again.";
  }
  return "Something got in the way. Please try again.";
}

export const authNotReady = "The door isn't ready yet. Please try again in a little while.";
