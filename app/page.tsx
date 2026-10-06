import type { Metadata } from "next";
import { HomePage } from "@/components/gosolo/home";

export const metadata: Metadata = {
  title: "Go Solo",
  description:
    "Go solo, not alone — a home for people building meaningful lives on their own terms.",
};

export default function Page() {
  return <HomePage />;
}
