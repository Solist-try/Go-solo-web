import { Frame, PageIntro, PrimaryLink } from "@/components/gosolo/pieces";

const pages = {
  experiences: {
    eyebrow: "Coming soon",
    title: "Experiences",
    lede: "Future real-world meetups and activities. Not open yet.",
    items: [
      ["Museum visits", "A room you can enter on your own."],
      ["Theatre nights", "One ticket, a good seat, no need to narrate the evening."],
      ["Workshops", "Hands busy, conversation optional."],
      ["Walks", "A route, a time, and the freedom to be quiet."],
      ["Day trips", "There and back, on purpose."],
    ],
  },
  partnerships: {
    eyebrow: "Coming soon",
    title: "Partnerships",
    lede: "Solo-friendly opportunities, when there are hosts who understand a table for one.",
    items: [
      ["Restaurants", "Tables that do not treat one person as half an order."],
      ["Museums", "Hours and tickets that assume you might come alone."],
      ["Travel", "Routes that do not require a second suitcase."],
      ["Learning", "Classes where arriving alone is the ordinary way."],
    ],
  },
  kits: {
    eyebrow: "Coming soon",
    title: "First Night Kits",
    lede: "Small companions for the first night of a new chapter. Teasers only, for now.",
    items: [
      ["Moving kit", "The first night in a place that does not know you yet."],
      ["Starting over kit", "For the week after a life changes shape."],
      ["Travel kit", "For the night you arrive somewhere with no one to text about the key."],
    ],
  },
} as const;

export function Horizon({ kind }: { kind: keyof typeof pages }) {
  const page = pages[kind];
  return (
    <Frame>
      <PageIntro eyebrow={page.eyebrow} title={page.title}>
        {page.lede}
      </PageIntro>
      <ul className="mt-10 grid gap-4 md:grid-cols-2">
        {page.items.map(([title, body]) => (
          <li key={title} className="rounded-[28px] border border-dashed border-ink/15 bg-white/50 p-7">
            <p className="text-sm text-ink-soft">Coming soon</p>
            <h2 className="mt-3 font-serif text-3xl">{title}</h2>
            <p className="mt-3 leading-relaxed text-ink-soft">{body}</p>
            <button type="button" disabled className="mt-6 h-12 cursor-not-allowed rounded-full bg-mist px-5 text-sm text-ink-soft">
              Not open yet
            </button>
          </li>
        ))}
      </ul>
      <div className="mt-12">
        <PrimaryLink href="/seeds">Meanwhile, find a seed</PrimaryLink>
      </div>
    </Frame>
  );
}
