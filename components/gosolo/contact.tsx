"use client";

import Link from "next/link";
import { useState, type FormEvent } from "react";
import { Frame, Panel, areaClass, fieldClass, pill } from "@/components/gosolo/pieces";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { useGoSolo } from "@/lib/gosolo";

export function ContactPage() {
  const { content, user, leaveNote } = useGoSolo();
  const [name, setName] = useState(user?.displayName ?? "");
  const [email, setEmail] = useState(user?.email ?? "");
  const [body, setBody] = useState("");
  const [error, setError] = useState("");
  const [sent, setSent] = useState(false);

  function onSubmit(event: FormEvent) {
    event.preventDefault();
    if (!name.trim() || !email.trim() || !body.trim()) {
      setError("A name, a way back to you, and a few sentences are enough.");
      return;
    }
    if (!email.includes("@")) {
      setError("That email does not look complete.");
      return;
    }
    const result = leaveNote({ name, email, body });
    if (result.error) {
      setError(result.error);
      return;
    }
    setError("");
    setSent(true);
  }

  return (
    <Frame>
      <header className="max-w-3xl">
        <h1 className="font-serif text-5xl leading-tight tracking-tight text-ink sm:text-7xl">Contact</h1>
        <p className="mt-6 text-xl leading-relaxed text-ink-soft sm:text-2xl">
          I read this. Write as you would to someone keeping a chair for you.
        </p>
      </header>

      <div className="mt-12 grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
        {sent ? (
          <Panel tone="sage">
            <h2 className="font-serif text-4xl tracking-tight">I have it.</h2>
            <p className="mt-4 text-lg leading-relaxed">
              Thank you for writing. I will read it as a person, not as a queue.
            </p>
            <p className="mt-6">
              <Link href="/about" className="underline decoration-ink/20 underline-offset-4">
                Back to why this place exists
              </Link>
            </p>
          </Panel>
        ) : (
          <form onSubmit={onSubmit} className="space-y-5">
            <label className="block space-y-2">
              <span className="text-sm">Your name</span>
              <Input className={fieldClass} value={name} maxLength={80} onChange={(event) => setName(event.target.value)} />
            </label>
            <label className="block space-y-2">
              <span className="text-sm">Email</span>
              <Input
                className={fieldClass}
                type="email"
                value={email}
                maxLength={120}
                onChange={(event) => setEmail(event.target.value)}
              />
            </label>
            <label className="block space-y-2">
              <span className="text-sm">Note</span>
              <Textarea
                className={areaClass}
                value={body}
                maxLength={2000}
                onChange={(event) => setBody(event.target.value)}
              />
            </label>
            {error ? (
              <p role="alert" className="text-sm text-destructive">
                {error}
              </p>
            ) : null}
            <Button type="submit" className={pill}>
              Send the note
            </Button>
          </form>
        )}

        <Panel>
          <h2 className="font-serif text-3xl tracking-tight">How to reach me</h2>
          <ul className="mt-4 space-y-3 text-lg leading-relaxed">
            <li>This note comes to me. I read it myself.</li>
            {content.founderEmail.trim() ? (
              <li>
                Or write directly:{" "}
                <a className="underline decoration-ink/20 underline-offset-4" href={`mailto:${content.founderEmail.trim()}`}>
                  {content.founderEmail.trim()}
                </a>
              </li>
            ) : (
              <li>Leave your email in the note if you want a reply in your own inbox.</li>
            )}
            <li>
              If you are already here, the{" "}
              <Link href="/campfire" className="underline decoration-ink/20 underline-offset-4">
                campfire
              </Link>{" "}
              is for conversation with other people. A private note belongs on this page.
            </li>
          </ul>
        </Panel>
      </div>
    </Frame>
  );
}
