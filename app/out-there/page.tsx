import type { Metadata } from "next";
import { StoryIndex } from "@/components/gosolo/stories";

export const metadata: Metadata = { title: "Out There" };

export default function Page() {
  return <StoryIndex />;
}
