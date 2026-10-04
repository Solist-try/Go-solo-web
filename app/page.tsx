import type { Metadata } from "next";
import { HomePage } from "@/components/gosolo/home";

export const metadata: Metadata = {
  title: "Go Solo",
  description:
    "Go Solo is a community and home base for people who live independently and want to do more with their lives. Find a seed, try it, share what happened, and meet people on a similar path.",
};

export default function Page() {
  return <HomePage />;
}
