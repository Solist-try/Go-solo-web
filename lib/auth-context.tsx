"use client";

import type { Session, User } from "@supabase/supabase-js";
import { createContext, useContext, useEffect, useMemo, useState, type ReactNode } from "react";
import { authNotReady, friendlyAuthError, getSupabase } from "@/lib/supabase";
import { isSupabaseConfigured, siteUrl } from "@/lib/supabase/env";

type AuthResult = { error?: string; needsVerification?: boolean };

type AuthContextValue = {
  user: User | null;
  session: Session | null;
  loading: boolean;
  configured: boolean;
  signIn: (email: string, password: string) => Promise<AuthResult>;
  signUp: (email: string, password: string) => Promise<AuthResult>;
  signOut: () => Promise<void>;
  resetPassword: (email: string) => Promise<AuthResult>;
};

const AuthContext = createContext<AuthContextValue | null>(null);

const redirectTo = (next: string) => `${siteUrl()}/auth/callback?next=${encodeURIComponent(next)}`;

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [session, setSession] = useState<Session | null>(null);
  const [loading, setLoading] = useState(true);
  const configured = isSupabaseConfigured();

  useEffect(() => {
    const supabase = getSupabase();
    if (!supabase) {
      setLoading(false);
      return;
    }

    let active = true;
    supabase.auth.getSession().then(({ data }) => {
      if (!active) return;
      setSession(data.session);
      setUser(data.session?.user ?? null);
      setLoading(false);
    });

    const { data } = supabase.auth.onAuthStateChange((_event, nextSession) => {
      setSession(nextSession);
      setUser(nextSession?.user ?? null);
      setLoading(false);
    });

    return () => {
      active = false;
      data.subscription.unsubscribe();
    };
  }, []);

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      session,
      loading,
      configured,
      async signIn(email, password) {
        const supabase = getSupabase();
        if (!supabase) return { error: authNotReady };
        const { error } = await supabase.auth.signInWithPassword({
          email: email.trim().toLowerCase(),
          password,
        });
        return { error: friendlyAuthError(error) ?? undefined };
      },
      async signUp(email, password) {
        const supabase = getSupabase();
        if (!supabase) return { error: authNotReady };
        const { data, error } = await supabase.auth.signUp({
          email: email.trim().toLowerCase(),
          password,
          options: { emailRedirectTo: redirectTo("/onboarding") },
        });
        return {
          error: friendlyAuthError(error) ?? undefined,
          needsVerification: !data.session,
        };
      },
      async signOut() {
        const supabase = getSupabase();
        if (!supabase) return;
        await supabase.auth.signOut();
        setSession(null);
        setUser(null);
      },
      async resetPassword(email) {
        const supabase = getSupabase();
        if (!supabase) return { error: authNotReady };
        const { error } = await supabase.auth.resetPasswordForEmail(email.trim().toLowerCase(), {
          redirectTo: redirectTo("/reset-password"),
        });
        return { error: friendlyAuthError(error) ?? undefined };
      },
    }),
    [user, session, loading, configured],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) throw new Error("useAuth must be used within AuthProvider");
  return context;
}
