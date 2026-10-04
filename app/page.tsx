import type { Metadata } from "next";
import { HomePage } from "@/components/gosolo/home";

export const metadata: Metadata = {
  title: "Go Solo",
  description:
    "Go Solo helps people build lives that work, whether or not somebody else shows up.",
};

export default function Page() {
  return <HomePage />;
}
