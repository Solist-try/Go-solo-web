"use client";

import Image from "next/image";
import Link from "next/link";
import { Frame } from "@/components/gosolo/pieces";
import { READING_CATEGORIES, readingsIn, type Reading, type ReadingCategory } from "@/lib/readings";

const tones = ["bg-sage", "bg-clay", "bg-gold", "bg-mist"] as const;

function CategoryPhoto({ category, priority = false }: { category: ReadingCategory; priority?: boolean }) {
  if (!category.image || !category.imageAlt) return null;
  return (
    <Image
      src={category.image}
      alt={category.imageAlt}
      width={1152}
      height={864}
      priority={priority}
      sizes="(min-width: 1024px) 40rem, 100vw"
      className="aspect-[4/3] w-full rounded-[28px] object-cover"
    />
  );
}

export function ReadingRoom() {
  return (
    <Frame>
      <header className="max-w-3xl">
        <h1 className="font-serif text-5xl leading-tight tracking-tight text-ink sm:text-7xl">Reading Room</h1>
        <p className="mt-6 text-xl leading-relaxed text-ink-soft sm:text-2xl">
          Collected ideas, reflections and resources for independent living.
        </p>
        <p className="mt-6 max-w-2xl text-lg leading-relaxed text-ink">
          This room is as important as Out There. Many people arrive looking for practical help before they are ready for a trip.
        </p>
        <ul className="mt-8 max-w-2xl space-y-2 text-lg text-ink">
          {[
            "How do I live independently?",
            "How do I make friends?",
            "How do I create routines?",
            "How do I manage a household?",
            "How do I build confidence?",
            "How do I do things alone?",
          ].map((question) => (
            <li key={question}>{question}</li>
          ))}
        </ul>
        <p className="mt-6 max-w-2xl text-lg leading-relaxed text-ink-soft">
          A quiet library beside the campfire. Practical, grounded, and useful. Read what helps, then go live.
        </p>
      </header>
      <div className="mt-14 space-y-16">
        {READING_CATEGORIES.map((category, index) => (
          <section key={category.id} id={category.id} className="scroll-mt-28">
            <div className={`grid gap-6 lg:gap-10 ${category.image ? "lg:grid-cols-12 lg:items-center" : ""}`}>
              {category.image ? (
                <figure className="lg:col-span-7">
                  <CategoryPhoto category={category} priority={index === 0} />
                </figure>
              ) : null}
              <div className={category.image ? "lg:col-span-5" : "max-w-3xl"}>
                <h2 className="font-serif text-4xl tracking-tight text-ink sm:text-5xl">{category.title}</h2>
                <p className="mt-3 text-lg leading-relaxed text-ink-soft">{category.line}</p>
              </div>
            </div>
            <ul className="mt-6 grid gap-3">
              {readingsIn(category.id).map((reading) => (
                <li key={reading.slug}>
                  <Link
                    href={`/reading-room/${reading.slug}`}
                    className={`block rounded-[28px] px-6 py-5 sm:px-7 ${tones[index % tones.length]}`}
                  >
                    <h3 className="font-serif text-2xl leading-snug tracking-tight text-ink sm:text-3xl">{reading.title}</h3>
                    <p className="mt-2 max-w-2xl text-base leading-relaxed text-ink">{reading.standfirst}</p>
                  </Link>
                </li>
              ))}
            </ul>
          </section>
        ))}
      </div>
    </Frame>
  );
}

export function ReadingPiece({ reading }: { reading: Reading }) {
  const category = READING_CATEGORIES.find((item) => item.id === reading.category);
  return (
    <Frame className="max-w-3xl">
      <p className="text-sm text-ink-soft">
        <Link href="/reading-room" className="underline decoration-ink/20 underline-offset-4">
          Reading Room
        </Link>
        {category ? (
          <>
            {" · "}
            <Link href={`/reading-room#${category.id}`} className="underline decoration-ink/20 underline-offset-4">
              {category.title}
            </Link>
          </>
        ) : null}
      </p>
      {category?.image ? (
        <figure className="mt-8">
          <CategoryPhoto category={category} priority />
        </figure>
      ) : null}
      <h1 className="mt-8 font-serif text-5xl leading-tight tracking-tight text-ink sm:text-6xl">{reading.title}</h1>
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
