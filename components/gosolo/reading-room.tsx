"use client";

import Link from "next/link";
import { Frame } from "@/components/gosolo/pieces";
import { READINGS, type Reading } from "@/lib/readings";

const tones = ["bg-sage", "bg-clay", "bg-gold", "bg-mist"] as const;

export function ReadingRoom() {
  return (
    <Frame>
      <header className="max-w-3xl">
        <h1 className="font-serif text-5xl leading-tight tracking-tight text-ink sm:text-7xl">Reading Room</h1>
        <p className="mt-6 text-xl leading-relaxed text-ink-soft sm:text-2xl">
          Collected ideas, reflections and resources for independent living.
        </p>
        <p className="mt-6 max-w-2xl text-lg leading-relaxed text-ink-soft">
          A quiet shelf beside the campfire. Nothing here is trying to keep you. Read what is useful, then go live.
        </p>
      </header>
      <ul className="mt-14 grid gap-4">
        {READINGS.map((reading, index) => (
          <li key={reading.slug}>
            <Link
              href={`/reading-room/${reading.slug}`}
              className={`block rounded-[28px] p-7 sm:p-9 ${tones[index % tones.length]}`}
            >
              <h2 className="font-serif text-4xl leading-tight tracking-tight text-ink">{reading.title}</h2>
              <p className="mt-4 max-w-2xl text-lg leading-relaxed text-ink">{reading.standfirst}</p>
            </Link>
          </li>
        ))}
      </ul>
    </Frame>
  );
}

export function ReadingPiece({ reading }: { reading: Reading }) {
  return (
    <Frame className="max-w-3xl">
      <p className="text-sm text-ink-soft">
        <Link href="/reading-room" className="underline decoration-ink/20 underline-offset-4">
          Reading Room
        </Link>
      </p>
      <h1 className="mt-4 font-serif text-5xl leading-tight tracking-tight text-ink sm:text-6xl">{reading.title}</h1>
      <p className="mt-6 text-xl leading-relaxed text-ink-soft">{reading.standfirst}</p>
      <div className="mt-10 space-y-6 text-lg leading-relaxed text-ink">
        {reading.paragraphs.map((paragraph) => (
          <p key={paragraph}>{paragraph}</p>
        ))}
      </div>
      <p className="mt-14 text-lg">
        <Link href="/reading-room" className="underline decoration-ink/20 underline-offset-4">
          Back to the shelf
        </Link>
      </p>
    </Frame>
  );
}
