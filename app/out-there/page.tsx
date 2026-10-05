import type { Metadata } from "next";
import { StoryIndex } from "@/components/gosolo/stories";

export const metadata: Metadata = {
  title: "Out There",
  description: "Real experiences brought back by members, and ideas kept separate from stories.",
};

export default function Page() {
  return <StoryIndex />;
}
