import type { Metadata } from "next";
import { StoryDetail } from "@/components/gosolo/stories";

export const metadata: Metadata = { title: "An experience" };

export default async function Page({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  return <StoryDetail id={id} />;
}
