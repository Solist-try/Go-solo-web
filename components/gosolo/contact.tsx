"use client";

import Link from "next/link";
import { useState, type FormEvent } from "react";
import { Frame, Panel, areaClass, fieldClass, pill } from "@/components/gosolo/pieces";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { useGoSolo } from "@/lib/gosolo";

export function ContactPage() {
  const { user, leaveNote } = useGoSolo();
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
      <header>
        <h1 className="font-serif text-5xl leading-tight tracking-tight text-ink sm:text-7xl">Contact</h1>
      </header>

      <div className="mt-16 grid gap-12 lg:mt-24 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)] lg:gap-16">
        <Panel className="lg:order-2">
          <h2 className="font-serif text-3xl tracking-tight text-ink">How to reach me</h2>
          <div className="mt-8 space-y-4 text-lg leading-relaxed text-ink">
            <p>Hi there, nice to hear from you.</p>
            <p>If you have ideas, questions, concerns, or stories, I&apos;d love to hear from you.</p>
            <p>You can reach me by writing a note.</p>
          </div>
          <p className="mt-8 text-base text-ink">Marge Aliaga</p>
          <p className="mt-2">
            <a
              href="mailto:marge@gosolo.co.network"
              className="text-lg underline decoration-ink/30 underline-offset-4 hover:decoration-ink"
            >
              marge@gosolo.co.network
            </a>
          </p>
        </Panel>

        {sent ? (
          <Panel tone="sage" className="lg:order-1">
            <h2 className="font-serif text-4xl tracking-tight">I have it.</h2>
            <p className="mt-4 text-lg leading-relaxed">Thank you for writing. I&apos;ll read it.</p>
            <p className="mt-6">
              <Link href="/about" className="underline decoration-ink/20 underline-offset-4">
                Back to why this place exists
              </Link>
            </p>
          </Panel>
        ) : (
          <form onSubmit={onSubmit} className="space-y-5 lg:order-1">
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

      </div>
    </Frame>
  );
}
