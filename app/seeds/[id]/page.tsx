import type { Metadata } from "next";
import { SeedDetail } from "@/components/gosolo/seeds";
import { getSeed } from "@/lib/catalog";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ id: string }>;
}): Promise<Metadata> {
  const { id } = await params;
  const seed = getSeed(id);
  return { title: seed?.title ?? "Seed", description: seed?.description };
}

export default async function Page({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  return <SeedDetail id={id} />;
}
