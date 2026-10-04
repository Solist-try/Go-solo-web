import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { ReadingPiece } from "@/components/gosolo/reading-room";
import { READINGS, getReading } from "@/lib/readings";

export function generateStaticParams() {
  return READINGS.map((reading) => ({ slug: reading.slug }));
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ slug: string }>;
}): Promise<Metadata> {
  const { slug } = await params;
  const reading = getReading(slug);
  return { title: reading?.title ?? "Reading Room" };
}

export default async function Page({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const reading = getReading(slug);
  if (!reading) notFound();
  return <ReadingPiece reading={reading} />;
}
