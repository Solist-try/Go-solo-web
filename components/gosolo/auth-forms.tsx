"use client";

import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { useEffect, useState, type FormEvent } from "react";
import { Frame, PageIntro, Panel, fieldClass, pill } from "@/components/gosolo/pieces";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { useGoSolo } from "@/lib/gosolo";
import { safeNext } from "@/lib/format";

function AuthFrame({
  title,
  children,
  lede,
}: {
  title: string;
  lede: string;
  children: React.ReactNode;
}) {
  return (
    <Frame className="max-w-3xl">
      <PageIntro title={title}>{lede}</PageIntro>
      <Panel className="mt-10">{children}</Panel>
    </Frame>
  );
}

export function LoginForm() {
  const { ready, user, login } = useGoSolo();
  const router = useRouter();
  const params = useSearchParams();
  const next = safeNext(params.get("next"));
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [pending, setPending] = useState(false);

  useEffect(() => {
    if (!ready || !user) return;
    if (!user.emailVerified) router.replace("/verify-email");
    else if (!user.onboardingComplete) router.replace("/onboarding");
    else router.replace(next);
  }, [ready, user, router, next]);

  async function onSubmit(event: FormEvent) {
    event.preventDefault();
    setPending(true);
    setError("");
    const result = await login(email, password);
    setPending(false);
    if (result.error) {
      setError(result.error);
      return;
    }
    if (!result.emailVerified) router.push("/verify-email");
    else if (!result.onboardingComplete) router.push("/onboarding");
    else router.push(next);
  }

  return (
    <AuthFrame title="Welcome back." lede="Come in quietly. Nothing here is asking you to catch up.">
      <form onSubmit={onSubmit} className="space-y-5">
        <label className="block space-y-2">
          <span className="text-sm">Email</span>
          <Input
            className={fieldClass}
            type="email"
            autoComplete="email"
            required
            value={email}
            onChange={(event) => setEmail(event.target.value)}
          />
        </label>
        <label className="block space-y-2">
          <span className="text-sm">Password</span>
          <Input
            className={fieldClass}
            type="password"
            autoComplete="current-password"
            required
            value={password}
            onChange={(event) => setPassword(event.target.value)}
          />
        </label>
        {error ? (
          <p role="alert" className="text-sm text-destructive">
            {error}
          </p>
        ) : null}
        <Button type="submit" className={pill} disabled={pending}>
          {pending ? "Opening the door…" : "Log in"}
        </Button>
        <p className="text-sm text-ink-soft">
          <Link href="/forgot-password" className="underline decoration-ink/20 underline-offset-4">
            Forgot your password?
          </Link>
        </p>
        <p className="text-sm text-ink-soft">
          New here?{" "}
          <Link href="/register" className="underline decoration-ink/20 underline-offset-4">
            Join Go Solo
          </Link>
        </p>
      </form>
    </AuthFrame>
  );
}

export function RegisterForm() {
  const { mode, register } = useGoSolo();
  const router = useRouter();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [confirm, setConfirm] = useState("");
  const [error, setError] = useState("");
  const [pending, setPending] = useState(false);

  async function onSubmit(event: FormEvent) {
    event.preventDefault();
    if (password !== confirm) {
      setError("Those passwords do not match.");
      return;
    }
    setPending(true);
    setError("");
    const result = await register(email, password);
    setPending(false);
    if (result.error) {
      setError(result.error);
      return;
    }
    router.push("/verify-email");
  }

  return (
    <AuthFrame
      title="Join Go Solo."
      lede="Create an account, then tell us a little about what brings you here."
    >
      <form onSubmit={onSubmit} className="space-y-5">
        <label className="block space-y-2">
          <span className="text-sm">Email</span>
          <Input
            className={fieldClass}
            type="email"
            autoComplete="email"
            required
            value={email}
            onChange={(event) => setEmail(event.target.value)}
          />
        </label>
        <label className="block space-y-2">
          <span className="text-sm">Password</span>
          <Input
            className={fieldClass}
            type="password"
            autoComplete="new-password"
            minLength={8}
            required
            value={password}
            onChange={(event) => setPassword(event.target.value)}
          />
          <span className="block text-sm text-ink-soft">At least 8 characters.</span>
        </label>
        <label className="block space-y-2">
          <span className="text-sm">Confirm password</span>
          <Input
            className={fieldClass}
            type="password"
            autoComplete="new-password"
            minLength={8}
            required
            value={confirm}
            onChange={(event) => setConfirm(event.target.value)}
          />
        </label>
        {error ? (
          <p role="alert" className="text-sm text-destructive">
            {error}
          </p>
        ) : null}
        <Button type="submit" className={pill} disabled={pending}>
          {pending ? "Creating your place…" : "Create account"}
        </Button>
        {mode === "demo" ? (
          <p className="text-sm leading-relaxed text-ink-soft">
            This preview keeps your account in this browser. Connect Supabase when you want accounts
            kept on a server, with real email verification.
          </p>
        ) : (
          <p className="text-sm leading-relaxed text-ink-soft">
            We will send one email to confirm this address. Nothing else will chase you.
          </p>
        )}
        <p className="text-sm text-ink-soft">
          Already have a place here?{" "}
          <Link href="/login" className="underline decoration-ink/20 underline-offset-4">
            Log in
          </Link>
        </p>
      </form>
    </AuthFrame>
  );
}

export function ForgotForm() {
  const { requestPasswordReset, mode } = useGoSolo();
  const [email, setEmail] = useState("");
  const [error, setError] = useState("");
  const [previewPath, setPreviewPath] = useState("");
  const [sent, setSent] = useState(false);
  const [pending, setPending] = useState(false);

  async function onSubmit(event: FormEvent) {
    event.preventDefault();
    setPending(true);
    setError("");
    const result = await requestPasswordReset(email);
    setPending(false);
    if (result.error) {
      setError(result.error);
      return;
    }
    setSent(true);
    setPreviewPath(result.previewPath ?? "");
  }

  return (
    <AuthFrame
      title="Reset your password."
      lede="If an account exists for that email, a reset path will open. We will not say whether the address is here."
    >
      {sent ? (
        <div className="space-y-4">
          <p className="text-lg leading-relaxed">
            {mode === "supabase"
              ? "Check your email for a reset link. It will bring you back here to choose a new password."
              : "If that email belongs to an account in this preview, you can continue below."}
          </p>
          {previewPath ? (
            <Button asChild className={pill}>
              <Link href={previewPath}>Choose a new password</Link>
            </Button>
          ) : null}
        </div>
      ) : (
        <form onSubmit={onSubmit} className="space-y-5">
          <label className="block space-y-2">
            <span className="text-sm">Email</span>
            <Input
              className={fieldClass}
              type="email"
              autoComplete="email"
              required
              value={email}
              onChange={(event) => setEmail(event.target.value)}
            />
          </label>
          {error ? (
            <p role="alert" className="text-sm text-destructive">
              {error}
            </p>
          ) : null}
          <Button type="submit" className={pill} disabled={pending}>
            {pending ? "Sending…" : "Send reset link"}
          </Button>
        </form>
      )}
    </AuthFrame>
  );
}

export function ResetForm() {
  const { resetPassword } = useGoSolo();
  const router = useRouter();
  const params = useSearchParams();
  const token = params.get("token") ?? "";
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [pending, setPending] = useState(false);

  async function onSubmit(event: FormEvent) {
    event.preventDefault();
    setPending(true);
    const result = await resetPassword(password, token);
    setPending(false);
    if (result.error) {
      setError(result.error);
      return;
    }
    router.push("/login");
  }

  return (
    <AuthFrame title="Choose a new password." lede="Something you can remember without turning your life into a vault.">
      <form onSubmit={onSubmit} className="space-y-5">
        <label className="block space-y-2">
          <span className="text-sm">New password</span>
          <Input
            className={fieldClass}
            type="password"
            autoComplete="new-password"
            minLength={8}
            required
            value={password}
            onChange={(event) => setPassword(event.target.value)}
          />
        </label>
        {error ? (
          <p role="alert" className="text-sm text-destructive">
            {error}
          </p>
        ) : null}
        <Button type="submit" className={pill} disabled={pending}>
          {pending ? "Saving…" : "Save password"}
        </Button>
      </form>
    </AuthFrame>
  );
}

export function VerifyEmail() {
  const { ready, user, mode, confirmEmail, resendVerification } = useGoSolo();
  const router = useRouter();
  const [message, setMessage] = useState("");

  useEffect(() => {
    if (!ready) return;
    if (!user && mode === "demo") router.replace("/login");
    if (user?.emailVerified && user.onboardingComplete) router.replace("/dashboard");
    else if (user?.emailVerified) router.replace("/onboarding");
  }, [ready, user, mode, router]);

  if (!ready) return null;

  return (
    <AuthFrame
      title="Confirm your email."
      lede="One note, so we know this address can find you. We will not turn it into a stream of reminders."
    >
      <div className="space-y-6">
        <p className="text-lg leading-relaxed">
          {mode === "supabase"
            ? `Open the message sent to ${user?.email || "your inbox"} and follow it back here.`
            : "This preview does not send email. Confirm the address to continue into onboarding."}
        </p>
        {user?.email ? <p className="text-sm text-ink-soft">{user.email}</p> : null}
        {mode === "demo" && user ? (
          <Button
            className={pill}
            onClick={async () => {
              await confirmEmail();
              router.push("/onboarding");
            }}
          >
            Confirm and continue
          </Button>
        ) : (
          <Button
            variant="outline"
            className={`${pill} bg-transparent`}
            onClick={async () => {
              const result = await resendVerification();
              setMessage(result.error || "Sent again. Give it a quiet minute.");
            }}
          >
            Send the email again
          </Button>
        )}
        {message ? <p className="text-sm text-ink-soft">{message}</p> : null}
      </div>
    </AuthFrame>
  );
}
