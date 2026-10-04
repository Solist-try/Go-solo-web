export type Reading = {
  slug: string;
  title: string;
  standfirst: string;
  paragraphs: string[];
};

export const READINGS: Reading[] = [
  {
    slug: "stop-waiting",
    title: "Stop waiting to begin",
    standfirst: "A life can start before the company arrives.",
    paragraphs: [
      "A lot of good days get filed under later. Later, when someone can come along. Later, when the calendar matches. Later, when it feels less conspicuous to walk in alone.",
      "Later is a comfortable place. It asks nothing today. The cost shows up slowly, as a list of things you meant to do and a feeling that your real life is still in the other room.",
      "Beginning does not require an audience. It requires a decision small enough to keep. One meal. One class. One night away. The company, if it comes, can meet you there.",
    ],
  },
  {
    slug: "independence-and-company",
    title: "Independence and company",
    standfirst: "Living alone and having people are not opposites.",
    paragraphs: [
      "Independence is often described as the absence of other people. That description is too thin. A person can keep their own keys, their own hours, and their own plans, and still want a table that is not always empty.",
      "The useful question is not whether you are alone. It is whether the life you are living has room for other people without handing them the steering.",
      "Connection works best when it is chosen. A conversation after a day you actually lived. A walk with someone who is also figuring it out. Not a replacement for the day. A companion to it.",
    ],
  },
  {
    slug: "small-enough-to-keep",
    title: "Small enough to keep",
    standfirst: "Most lives change by something you can finish this week.",
    paragraphs: [
      "Grand plans have a way of staying grand. They sound like a person you might become, which is a hard person to be on a Tuesday.",
      "A smaller action has a different quality. You can tell whether you did it. You can say what happened, including the parts that were dull or awkward. You can decide if you would do it again.",
      "That is enough to learn from. A life gets bigger by repetition of things that fit in an ordinary week, not by waiting until you feel like a different person.",
    ],
  },
  {
    slug: "leave-the-house",
    title: "Leave the house",
    standfirst: "The interesting part is rarely on the screen.",
    paragraphs: [
      "It is possible to feel very accompanied and still not go anywhere. Messages are easy. Rooms are not. A museum, a class, a train, a kitchen that is not yours: these ask you to be a body in a place.",
      "Nothing here is against writing things down or talking them through. The campfire is for that. It is simply a poor substitute for the thing itself.",
      "If a plan only lives in a thread, it has not happened yet. Go out. Then, if you want, tell someone what it was actually like.",
    ],
  },
  {
    slug: "a-community-that-fits",
    title: "A community that fits around a life",
    standfirst: "People can support a life without becoming the whole of it.",
    paragraphs: [
      "Some communities want your constant presence. They measure you by how often you return, how visible you are, how quickly you reply. That kind of room becomes another job.",
      "A better room is one you can leave. It keeps your chair. It does not punish the week you were busy living. When you come back, you are not catching up. You are arriving with something true.",
      "Use a community to start, to recover, to compare notes, to find one person who understands the terrain. Then go back to your own kitchen. The life is the point. The room is the support.",
    ],
  },
  {
    slug: "no-permission-required",
    title: "No permission required",
    standfirst: "You do not need a witness before you are allowed to try.",
    paragraphs: [
      "Permission is a habit dressed up as a practical concern. Who will I go with? What will people think? Is this the sort of thing a person does alone?",
      "Most of the time, the answer is ordinary. Restaurants seat one. Classes take one. Trains do not check whether you have a plus-one. The awkwardness, when it shows up, is usually smaller than the weeks spent arranging around it.",
      "You can still want company. Wanting it is not the same as waiting for it. Begin. If someone wants to come next time, there will be a next time because you already know the way.",
    ],
  },
];

export function getReading(slug: string) {
  return READINGS.find((reading) => reading.slug === slug);
}
