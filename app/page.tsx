import type { Metadata } from "next";
import { HomePage } from "@/components/gosolo/home";

export const metadata: Metadata = {
  title: "Go Solo",
  description:
    "Hello, Vagabond. Your life doesn't have to wait. Go Solo helps people explore, connect and grow while living independently.",
};

export default function Page() {
  return <HomePage />;
}
